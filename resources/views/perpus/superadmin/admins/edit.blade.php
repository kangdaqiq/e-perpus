@extends('perpus.layouts.app')

@section('title', 'Ubah Admin Sekolah')

@section('content')
<div class="mb-8">
    <a href="{{ route('superadmin.admins.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-slate-600 transition-colors mb-4">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Kembali ke Daftar Admin</span>
    </a>
    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-slate-100">Ubah Data Admin Sekolah</h2>
    <p class="text-xs md:text-sm text-slate-400 font-medium">Perbarui kredensial atau asosiasi sekolah dari admin terkait.</p>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 max-w-2xl shadow-sm">
    <form action="{{ route('superadmin.admins.update', $admin->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Pilih Sekolah -->
        <div>
            <label for="school_id" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Sekolah *</label>
            <select name="school_id" id="school_id" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('school_id') border-rose-500 @enderror">
                <option value="" disabled>-- Pilih Sekolah --</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id', $admin->school_id) == $school->id ? 'selected' : '' }}>
                        {{ $school->name }} {{ !$school->is_perpus_active ? '(E-Perpus Tidak Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('school_id')
                <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Lengkap -->
        <div>
            <label for="full_name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap *</label>
            <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $admin->full_name) }}" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('full_name') border-rose-500 @enderror">
            @error('full_name')
                <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Username -->
            <div>
                <label for="username" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Username *</label>
                <input type="text" name="username" id="username" value="{{ old('username', $admin->username) }}" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('username') border-rose-500 @enderror">
                @error('username')
                    <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email *</label>
                <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password (Kosongkan jika tidak diubah)</label>
            <input type="password" name="password" id="password" placeholder="Minimal 6 karakter" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('password') border-rose-500 @enderror">
            @error('password')
                <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
            <a href="{{ route('superadmin.admins.index') }}" class="px-6 py-3 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-95 transition-all duration-150">
                Batal
            </a>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 active:scale-95 transition-all duration-200">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
