<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSchoolPerpusActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Super Admin has no school constraints
            if ($user->isSuperAdmin()) {
                return $next($request);
            }

            // Check school and active status
            $school = $user->school;
            if (!$school || !$school->is_perpus_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'login' => 'Akses E-Perpus untuk sekolah Anda belum aktif atau telah dinonaktifkan. Silakan hubungi Super Admin.'
                ]);
            }
        }

        return $next($request);
    }
}
