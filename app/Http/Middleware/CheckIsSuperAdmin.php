<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIsSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Halaman ini hanya untuk Super Admin.');
        }

        return $next($request);
    }
}
