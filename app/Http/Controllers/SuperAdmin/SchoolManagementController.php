<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School;

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
}
