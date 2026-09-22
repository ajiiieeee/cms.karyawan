<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QueueController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Queue processor — no auth, protected by QUEUE_SECRET_TOKEN
// Usage: POST /api/queue/process -H "X-Queue-Token: <token>"
// Or:    POST /api/queue/process?secret=<token>
Route::post('/queue/process', [QueueController::class, 'processQueue'])
    ->name('queue.process');

