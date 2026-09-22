<?php

namespace App\Http\Middleware;

use App\Models\MenuAkses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Handle an incoming request.
     * Usage in routes: middleware('menu.access:menu-management,view')
     * 
     * @param string $menuLink  The menu link identifier (e.g., 'user-management')
     * @param string $action    The access type: view, add, edit, delete
     */
    public function handle(Request $request, Closure $next, string $menuLink, string $action = 'view'): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        if (!in_array($action, ['view', 'add', 'edit', 'delete'])) {
            abort(403, 'Aksi tidak valid.');
        }

        $hasAccess = MenuAkses::where('grup_id', $user->grup_id)
            ->whereHas('menu', function ($q) use ($menuLink) {
                $q->where('link', $menuLink);
            })
            ->where($action, 1)
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki izin untuk aksi ini.');
        }

        return $next($request);
    }
}
