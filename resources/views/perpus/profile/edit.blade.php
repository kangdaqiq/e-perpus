@extends('perpus.layouts.app')

@section('title', 'Ubah Profil')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-slate-100">Ubah Profil</h2>
    <p class="text-xs md:text-sm text-slate-400 font-medium">Perbarui informasi profil dan kredensial login Anda.</p>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 max-w-2xl shadow-sm">
    <form action="{{ route('perpus.profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Nama Lengkap -->
        <div>
            <label for="full_name" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap</label>
            <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $user->full_name) }}" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('full_name') border-rose-500 @enderror">
            @error('full_name')
                <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Username -->
            <div>
                <label for="username" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Username</label>
                <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('username') border-rose-500 @enderror">
                @error('username')
                    <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Password Baru -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" id="password" placeholder="Minimal 6 karakter" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200 @error('password') border-rose-500 @enderror">
                @error('password')
                    <p class="text-rose-500 text-xs font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Ketik ulang password baru" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 active:scale-95 transition-all duration-200">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
