<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolManagementController extends Controller
{
    /**
     * Tampilkan daftar sekolah dan status keaktifan e-perpus.
     */
    public function index()
    {
        $schools = School::withCount([
            'users',
            'members',
        ])->get();

        return view('perpus.superadmin.schools.index', compact('schools'));
    }

    /**
     * Aktifkan atau nonaktifkan status e-perpus sekolah.
     */
    public function toggleActive($id)
    {
        $school = School::findOrFail($id);
        $school->is_perpus_active = !$school->is_perpus_active;
        $school->save();

        $statusStr = $school->is_perpus_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Fitur E-Perpus untuk sekolah {$school->name} berhasil {$statusStr}.");
    }

    /**
     * Direct login ke sekolah/tenant tertentu sebagai admin sekolah.
     */
    public function impersonate($id)
    {
        $currentUser = auth()->user();

        if (!$currentUser->isSuperAdmin() && !session()->has('impersonator_id')) {
            abort(403, 'Hanya Super Admin yang dapat melakukan Direct Login ke tenant.');
        }

        $school = School::findOrFail($id);

        // Cari admin utama sekolah ini
        $adminUser = User::where('school_id', $school->id)
            ->where('role', 'admin')
            ->first();

        // Jika belum ada admin, buatkan akun admin otomatis
        if (!$adminUser) {
            $adminUser = User::create([
                'school_id'     => $school->id,
                'full_name'     => 'Admin ' . $school->name,
                'username'      => 'admin_school_' . $school->id,
                'email'         => 'admin_school_' . $school->id . '@eperpus.com',
                'password_hash' => Hash::make(Str::random(16)),
                'role'          => 'admin',
            ]);
        }

        // Simpan ID Super Admin asli di session jika belum diset
        if (!session()->has('impersonator_id')) {
            session(['impersonator_id' => $currentUser->id]);
        }

        Auth::login($adminUser);

        return redirect()->route('perpus.dashboard')
            ->with('success', 'Berhasil melakukan Direct Login ke tenant: ' . $school->name);
    }

    /**
     * Selesaikan mode impersonasi dan kembali ke akun Super Admin.
     */
    public function leaveImpersonation()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('perpus.dashboard');
        }

        $superAdminId = session('impersonator_id');
        session()->forget('impersonator_id');

        $superAdmin = User::find($superAdminId);
        if ($superAdmin) {
            Auth::login($superAdmin);
            return redirect()->route('superadmin.schools.index')
                ->with('success', 'Kembali ke akun Super Admin.');
        }

        return redirect()->route('login');
    }
}
