@extends('perpus.layouts.app')

@section('title', 'Dashboard Super Admin')

@section('content')
<!-- Welcome banner -->
<div class="mb-8 p-6 md:p-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl text-white shadow-xl shadow-indigo-600/20 flex flex-col md:flex-row items-center justify-between gap-6">
    <div>
        <h2 class="text-2xl md:text-3xl font-bold mb-2">Halo, {{ auth()->user()->full_name }}!</h2>
        <p class="text-indigo-100 text-sm md:text-base font-medium max-w-xl">Selamat datang di Dashboard Super Admin E-Perpus. Kelola perpustakaan multi-tenant, pantau statistik lintas sekolah, dan lakukan sinkronisasi data master secara terpusat.</p>
    </div>
    <div class="flex-shrink-0">
        <form action="{{ route('perpus.sync') }}" method="POST">
            @csrf
            <button type="submit" class="px-6 py-3 bg-white text-indigo-600 font-bold rounded-2xl shadow-lg hover:bg-indigo-50 active:scale-95 transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate animate-spin-slow"></i>
                <span>Sinkron Semua Tenant</span>
            </button>
        </form>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 md:gap-6 mb-8">
    <!-- Stat 1: Total Sekolah -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 bg-indigo-50 dark:bg-indigo-950/50 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg flex-shrink-0">
            <i class="fa-solid fa-school"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Sekolah</p>
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($totalSchools) }}</h3>
        </div>
    </div>

    <!-- Stat 2: Total Buku -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 bg-violet-50 dark:bg-violet-950/50 rounded-2xl flex items-center justify-center text-violet-600 dark:text-violet-400 text-lg flex-shrink-0">
            <i class="fa-solid fa-book"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Buku</p>
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($totalBooks) }}</h3>
        </div>
    </div>

    <!-- Stat 3: Peminjaman Aktif -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg flex-shrink-0">
            <i class="fa-solid fa-handshake"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pinjam Aktif</p>
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($activeLoans) }}</h3>
        </div>
    </div>

    <!-- Stat 4: Kunjungan Hari Ini -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 bg-amber-50 dark:bg-amber-950/50 rounded-2xl flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg flex-shrink-0">
            <i class="fa-solid fa-users-rectangle"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kunjungan Hari Ini</p>
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($todayVisits) }}</h3>
        </div>
    </div>

    <!-- Stat 5: Total Users -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 bg-blue-50 dark:bg-blue-950/50 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg flex-shrink-0">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Admin</p>
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($totalUsers) }}</h3>
        </div>
    </div>

    <!-- Stat 6: Total Members -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-11 h-11 bg-pink-50 dark:bg-pink-950/50 rounded-2xl flex items-center justify-center text-pink-600 dark:text-pink-400 text-lg flex-shrink-0">
            <i class="fa-solid fa-id-card"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Anggota</p>
            <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($totalMembers) }}</h3>
        </div>
    </div>
</div>

<!-- Table: Ringkasan Tenant Sekolah -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
    <div class="p-6 border-b border-slate-100 dark:border-slate-800">
        <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Status & Ringkasan Data Sekolah</h3>
        <p class="text-xs text-slate-400 mt-1">Daftar tenant sekolah yang terdaftar dan volume data terkait.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                    <th class="p-6">ID Sekolah</th>
                    <th class="p-6">Nama Sekolah</th>
                    <th class="p-6 text-center">Total Admin</th>
                    <th class="p-6 text-center">Total Anggota</th>
                    <th class="p-6 text-center">Total Buku</th>
                    <th class="p-6 text-center">Pengunjung Hari Ini</th>
                    <th class="p-6 text-center">Pinjam Aktif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                @forelse($schools as $school)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="p-6 font-semibold text-slate-500">{{ $school->id }}</td>
                        <td class="p-6">
                            <div class="font-bold text-slate-950 dark:text-slate-100">{{ $school->name }}</div>
                        </td>
                        <td class="p-6 text-center text-slate-800 dark:text-slate-200">{{ number_format($school->users_count) }}</td>
                        <td class="p-6 text-center text-slate-800 dark:text-slate-200">{{ number_format($school->members_count) }}</td>
                        <td class="p-6 text-center text-slate-800 dark:text-slate-200">{{ number_format($school->books_count) }}</td>
                        <td class="p-6 text-center">
                            <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 text-xs rounded-full font-bold">
                                {{ number_format($school->today_visits_count) }}
                            </span>
                        </td>
                        <td class="p-6 text-center">
                            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 text-xs rounded-full font-bold">
                                {{ number_format($school->active_loans_count) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400 font-medium">Belum ada sekolah terdaftar. Silakan sinkronisasikan data absensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
