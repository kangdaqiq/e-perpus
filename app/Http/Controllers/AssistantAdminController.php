<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AssistantAdminController extends Controller
{
    /**
     * Tampilkan daftar Admin Pembantu untuk sekolah yang aktif.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user->canManageAssistantAdmins()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola Admin Pembantu.');
        }

        $assistants = User::where('school_id', $user->school_id)
            ->where('role', 'admin_pembantu')
            ->latest()
            ->get();

        $count = $assistants->count();

        return view('perpus.assistant_admins.index', compact('assistants', 'count'));
    }

    /**
     * Simpan akun Admin Pembantu baru.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->canManageAssistantAdmins()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola Admin Pembantu.');
        }

        $currentCount = User::where('school_id', $user->school_id)
            ->where('role', 'admin_pembantu')
            ->count();

        if ($currentCount >= 2) {
            return redirect()->back()
                ->with('error', 'Jumlah akun Admin Pembantu telah mencapai batas maksimal (2 akun).')
                ->withInput();
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'username'  => 'required|string|max:50|unique:users,username',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
        ]);

        User::create([
            'school_id'     => $user->school_id,
            'full_name'     => $request->full_name,
            'username'      => $request->username,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => 'admin_pembantu',
        ]);

        return redirect()->route('perpus.assistant-admins.index')
            ->with('success', 'Akun Admin Pembantu berhasil ditambahkan.');
    }

    /**
     * Perbarui data Admin Pembantu.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->canManageAssistantAdmins()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola Admin Pembantu.');
        }

        $assistant = User::where('school_id', $user->school_id)
            ->where('role', 'admin_pembantu')
            ->findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'username'  => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($assistant->id)],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($assistant->id)],
            'password'  => 'nullable|string|min:6',
        ]);

        $data = [
            'full_name' => $request->full_name,
            'username'  => $request->username,
            'email'     => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        $assistant->update($data);

        return redirect()->route('perpus.assistant-admins.index')
            ->with('success', 'Data Admin Pembantu berhasil diperbarui.');
    }

    /**
     * Hapus akun Admin Pembantu.
     */
    public function destroy($id)
    {
        $user = auth()->user();

        if (!$user->canManageAssistantAdmins()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola Admin Pembantu.');
        }

        $assistant = User::where('school_id', $user->school_id)
            ->where('role', 'admin_pembantu')
            ->findOrFail($id);

        $assistant->delete();

        return redirect()->route('perpus.assistant-admins.index')
            ->with('success', 'Akun Admin Pembantu berhasil dihapus.');
    }
}
