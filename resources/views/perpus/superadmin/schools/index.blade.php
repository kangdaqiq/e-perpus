@extends('perpus.layouts.app')

@section('title', 'Kelola Sekolah')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-slate-100">Kelola Sekolah</h2>
        <p class="text-xs md:text-sm text-slate-400 font-medium">Aktifkan atau nonaktifkan modul E-Perpus untuk sekolah yang terdaftar.</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                    <th class="p-6">ID Sekolah</th>
                    <th class="p-6">Nama Sekolah</th>
                    <th class="p-6 text-center">Status E-Perpus</th>
                    <th class="p-6 text-center">Total Admin</th>
                    <th class="p-6 text-center">Total Anggota</th>
                    <th class="p-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                @forelse($schools as $school)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="p-6 font-semibold text-slate-500">{{ $school->id }}</td>
                        <td class="p-6">
                            <div class="font-bold text-slate-950 dark:text-slate-100">{{ $school->name }}</div>
                        </td>
                        <td class="p-6 text-center">
                            @if($school->is_perpus_active)
                                <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs rounded-full font-bold">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-xs rounded-full font-bold">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="p-6 text-center text-slate-800 dark:text-slate-200">{{ number_format($school->users_count) }}</td>
                        <td class="p-6 text-center text-slate-800 dark:text-slate-200">{{ number_format($school->members_count) }}</td>
                        <td class="p-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('superadmin.schools.toggle-active', $school->id) }}" method="POST" class="inline flex-shrink-0">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl border transition-all duration-150 {{ $school->is_perpus_active ? 'border-rose-200 dark:border-rose-800 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20' : 'border-emerald-200 dark:border-emerald-800 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }}">
                                        {{ $school->is_perpus_active ? 'Nonaktifkan E-Perpus' : 'Aktifkan E-Perpus' }}
                                    </button>
                                </form>
                                <a href="{{ route('superadmin.admins.index', ['school_id' => $school->id]) }}" class="px-4 py-2 text-xs font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-300 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all duration-150 flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-shield"></i>
                                    <span>Kelola Admin</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-400 font-medium">Belum ada sekolah terdaftar. Silakan lakukan sinkronisasi data terlebih dahulu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
