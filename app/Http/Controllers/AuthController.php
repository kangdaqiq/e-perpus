<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('perpus.dashboard');
        }
        return view('perpus.auth.login');
    }

    /**
     * Proses autentikasi user.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari berdasarkan email atau username
        $user = User::where('email', $request->login)
            ->orWhere('username', $request->login)
            ->first();

        if ($user && Hash::check($request->password, $user->password_hash)) {
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();

            return redirect()->route('perpus.dashboard')->with('success', 'Selamat datang kembali, ' . $user->full_name);
        }

        return redirect()->back()
            ->withErrors(['login' => 'Username/Email atau Password Anda salah.'])
            ->withInput($request->only('login', 'remember'));
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar dari sistem.');
    }
}
