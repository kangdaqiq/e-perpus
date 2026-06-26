@extends('perpus.layouts.app')

@section('title', 'Kelola Admin Sekolah')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl md:text-3xl font-bold text-slate-800 dark:text-slate-100">Kelola Admin Sekolah</h2>
        <p class="text-xs md:text-sm text-slate-400 font-medium">Manajemen data user admin dari tiap sekolah secara lokal.</p>
    </div>
    <div class="flex-shrink-0">
        <a href="{{ route('superadmin.admins.create') }}" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 active:scale-95 transition-all duration-200 flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Tambah Admin Baru</span>
        </a>
    </div>
</div>

<!-- Filter Box -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl mb-6 shadow-sm">
    <form action="{{ route('superadmin.admins.index') }}" method="GET" class="flex flex-col md:flex-row items-end gap-4">
        <div class="flex-1 w-full">
            <label for="school_id" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Filter Sekolah</label>
            <select name="school_id" id="school_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500 transition-all duration-200">
                <option value="">-- Tampilkan Semua Sekolah --</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }} {{ !$school->is_perpus_active ? '(Perpus Tidak Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex-shrink-0 w-full md:w-auto flex gap-2">
            <button type="submit" class="w-full md:w-auto px-6 py-3 bg-slate-800 dark:bg-slate-700 text-white font-bold rounded-2xl hover:bg-slate-900 transition-all duration-150">
                Filter
            </button>
            @if(request()->filled('school_id'))
                <a href="{{ route('superadmin.admins.index') }}" class="w-full md:w-auto px-6 py-3 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-150 text-center">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                    <th class="p-6">Nama Lengkap</th>
                    <th class="p-6">Username</th>
                    <th class="p-6">Email</th>
                    <th class="p-6">Sekolah</th>
                    <th class="p-6 text-center">Status Perpus Sekolah</th>
                    <th class="p-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                @forelse($admins as $admin)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="p-6">
                            <div class="font-bold text-slate-950 dark:text-slate-100">{{ $admin->full_name }}</div>
                        </td>
                        <td class="p-6 text-slate-600 dark:text-slate-400 font-semibold">{{ $admin->username }}</td>
                        <td class="p-6 text-slate-600 dark:text-slate-400">{{ $admin->email }}</td>
                        <td class="p-6 font-semibold">{{ $admin->school->name ?? 'Tidak Terkait' }}</td>
                        <td class="p-6 text-center">
                            @if($admin->school)
                                @if($admin->school->is_perpus_active)
                                    <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs rounded-full font-bold">Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-xs rounded-full font-bold">Tidak Aktif</span>
                                @endif
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="p-6 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('superadmin.admins.edit', $admin->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/20 rounded-xl transition-all duration-150" title="Ubah Data">
                                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                                </a>
                                <form action="{{ route('superadmin.admins.destroy', $admin->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all duration-150" title="Hapus">
                                        <i class="fa-solid fa-trash-can text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-400 font-medium">Belum ada user admin terdaftar untuk kriteria filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
