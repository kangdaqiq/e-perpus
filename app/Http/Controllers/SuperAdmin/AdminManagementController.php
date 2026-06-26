<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    /**
     * Tampilkan daftar user admin sekolah.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin')->with('school');

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $admins = $query->latest()->get();
        $schools = School::orderBy('name')->get();

        return view('perpus.superadmin.admins.index', compact('admins', 'schools'));
    }

    /**
     * Tampilkan form tambah admin.
     */
    public function create()
    {
        $schools = School::orderBy('name')->get();
        return view('perpus.superadmin.admins.create', compact('schools'));
    }

    /**
     * Simpan admin baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'full_name' => 'required|string|max:255',
            'username'  => 'required|string|max:50|unique:users,username',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
        ]);

        User::create([
            'school_id'     => $request->school_id,
            'full_name'     => $request->full_name,
            'username'      => $request->username,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => 'admin',
        ]);

        return redirect()->route('superadmin.admins.index')->with('success', 'Admin sekolah berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit admin.
     */
    public function edit($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        $schools = School::orderBy('name')->get();
        return view('perpus.superadmin.admins.edit', compact('admin', 'schools'));
    }

    /**
     * Perbarui data admin.
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'full_name' => 'required|string|max:255',
            'username'  => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($admin->id)],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'password'  => 'nullable|string|min:6',
        ]);

        $data = [
            'school_id' => $request->school_id,
            'full_name' => $request->full_name,
            'username'  => $request->username,
            'email'     => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        $admin->update($data);

        return redirect()->route('superadmin.admins.index')->with('success', 'Data admin sekolah berhasil diperbarui.');
    }

    /**
     * Hapus admin.
     */
    public function destroy($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        $admin->delete();

        return redirect()->route('superadmin.admins.index')->with('success', 'Admin sekolah berhasil dihapus.');
    }
}
