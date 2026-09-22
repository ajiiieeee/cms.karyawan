<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class QueueController extends Controller
{
    /**
     * Process queue jobs via HTTP request
     * This is used for shared hosting without terminal access
     * 
     * Security measures:
     * 1. Secret token validation (via header or query)
     * 2. Rate limiting (max 20 requests per minute)
     * 3. Optional IP whitelist
     * 4. Process locking to prevent overlap
     */
    public function processQueue(Request $request)
    {
        // 1. Rate limiting - max 20 requests per minute per IP
        $clientIp = $request->ip();
        $rateLimitKey = 'queue_process_' . $clientIp;
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'error' => 'Too many requests',
                'retry_after' => $seconds
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 60);

        // 2. Check IP Whitelist first (if configured, skip token check for whitelisted IPs)
        $allowedIps = array_filter(array_map('trim', explode(',', env('QUEUE_ALLOWED_IPS', ''))));
        $isWhitelistedIp = !empty($allowedIps) && in_array($clientIp, $allowedIps);

        // 3. Verify secret token (skip if IP is whitelisted)
        if (!$isWhitelistedIp) {
            $secret = $request->header('X-Queue-Token') ?? $request->query('secret');
            $validSecret = env('QUEUE_SECRET_TOKEN', '');

            // Fail-closed: reject if token not configured or doesn't match
            if (empty($validSecret) || empty($secret) || !hash_equals($validSecret, $secret)) {
                $this->logSecurityEvent("Unauthorized queue access attempt from IP: {$clientIp}");
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // 4. Check if another process is already running (prevent overlap)
        $lockKey = 'queue_processing_lock';
        if (Cache::has($lockKey)) {
            return response()->json([
                'success' => true,
                'processed' => 0,
                'message' => 'Another process is still running',
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        // 5. Check if there are any pending jobs first
        $pendingJobs = DB::table('jobs')->count();
        if ($pendingJobs === 0) {
            return response()->json([
                'success' => true,
                'processed' => 0,
                'message' => 'No pending jobs',
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        // Set lock for 5 minutes
        Cache::put($lockKey, true, now()->addMinutes(5));

        $processed = 0;
        $maxJobs = min($pendingJobs, 3);
        
        try {
            for ($i = 0; $i < $maxJobs; $i++) {
                $exitCode = Artisan::call('queue:work', [
                    '--once' => true,
                    '--tries' => 3,
                    '--timeout' => 90,
                    '--memory' => 128,
                ]);
                
                if ($exitCode === 0) {
                    $processed++;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Queue processing error: ' . $e->getMessage());
        } finally {
            Cache::forget($lockKey);
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'remaining' => max(0, $pendingJobs - $processed),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    /**
     * Log security events to dedicated file
     */
    private function logSecurityEvent(string $message): void
    {
        $logPath = storage_path('logs/queue-security.log');
        $datetime = now()->format('Y-m-d H:i:s');
        $logMessage = "[{$datetime}] {$message}" . PHP_EOL;
        
        file_put_contents($logPath, $logMessage, FILE_APPEND | LOCK_EX);
    }
}
