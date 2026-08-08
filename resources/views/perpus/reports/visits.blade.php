@extends('perpus.layouts.app')

@section('title', 'Rekap Kunjungan Perpustakaan')

@section('content')
<!-- Header Banner -->
<div class="mb-8 p-6 md:p-8 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-3xl text-white shadow-xl shadow-indigo-600/20 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2.5 bg-white/10 rounded-2xl backdrop-blur-md">
                <i class="fa-solid fa-file-chart-column text-2xl"></i>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold">Rekap Kunjungan Perpustakaan</h2>
        </div>
        <p class="text-indigo-100 text-sm md:text-base font-medium max-w-2xl">
            Laporan dan rekapitulasi data pengunjung perpustakaan {{ $school->name ?? '' }}. Gunakan filter kelas, jurusan, dan tanggal untuk menyajikan data terperinci.
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('perpus.reports.visits.print', request()->query()) }}" target="_blank" class="px-5 py-3 bg-white text-indigo-700 font-bold rounded-2xl shadow-lg hover:bg-indigo-50 active:scale-95 transition-all duration-200 flex items-center gap-2 text-sm">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Laporan</span>
        </a>
    </div>
</div>

<!-- Filter Box -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 mb-8 shadow-sm">
    <form method="GET" action="{{ route('perpus.reports.visits') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Start Date -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Dari Tanggal</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <!-- End Date -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <!-- Filter Kelas -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Filter Kelas</label>
            <select name="kelas" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">-- Semua Kelas --</option>
                @foreach($kelasList as $itemK)
                    <option value="{{ $itemK }}" {{ $kelas === $itemK ? 'selected' : '' }}>Kelas {{ $itemK }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Jurusan -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Filter Jurusan</label>
            <select name="jurusan" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">-- Semua Jurusan --</option>
                @foreach($jurusanList as $itemJ)
                    <option value="{{ $itemJ }}" {{ $jurusan === $itemJ ? 'selected' : '' }}>{{ $itemJ }}</option>
                @endforeach
            </select>
        </div>

        <!-- Search & Submit -->
        <div class="sm:col-span-2 lg:col-span-1 flex items-end gap-2">
            <div class="flex-grow">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Cari Nama / NIS</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIS..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition-all duration-150 shadow-sm flex items-center justify-center">
                <i class="fa-solid fa-filter"></i>
            </button>
            <a href="{{ route('perpus.reports.visits') }}" class="px-3 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold rounded-xl text-sm transition-all duration-150 flex items-center justify-center" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/50 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Kunjungan</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($totalVisits) }}</h3>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Siswa</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($studentVisits) }}</h3>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/50 rounded-2xl flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-chalkboard-user"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Guru / Staf</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($teacherVisits) }}</h3>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-2xl flex items-center justify-center text-purple-600 dark:text-purple-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-user-tag"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tamu Manual</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($guestVisits) }}</h3>
        </div>
    </div>
</div>

<!-- Table Rekap -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div>
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Daftar Rekapitulasi Kunjungan</h3>
            <p class="text-xs text-slate-400 mt-1">Periode {{ \Carbon\Carbon::parse($startDate)->isoFormat('DD MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('DD MMMM YYYY') }}</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                    <th class="p-4 pl-6">No</th>
                    <th class="p-4">Waktu Scan</th>
                    <th class="p-4">NIS / NIP</th>
                    <th class="p-4">Nama Pengunjung</th>
                    <th class="p-4">Kategori / Role</th>
                    <th class="p-4">Kelas / Jurusan</th>
                    <th class="p-4 pr-6">Keperluan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                @forelse($visits as $index => $visit)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="p-4 pl-6 text-slate-400 font-semibold">{{ $visits->firstItem() + $index }}</td>
                        <td class="p-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                            {{ \Carbon\Carbon::parse($visit->scanned_at)->isoFormat('DD MMM YYYY, HH:mm') }}
                        </td>
                        <td class="p-4 font-mono text-slate-500">
                            {{ $visit->member ? $visit->member->member_code : '-' }}
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-900 dark:text-slate-100">
                                {{ $visit->member ? $visit->member->name : ($visit->visitor_name ?? 'Tamu') }}
                            </div>
                        </td>
                        <td class="p-4">
                            @if($visit->member)
                                @if($visit->member->source_type === 'siswa')
                                    <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs rounded-full font-bold">Siswa</span>
                                @else
                                    <span class="px-2.5 py-1 bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 text-xs rounded-full font-bold">Guru / Staf</span>
                                @endif
                            @else
                                <span class="px-2.5 py-1 bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 text-xs rounded-full font-bold">Tamu Manual</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs rounded-lg font-semibold">
                                {{ $visit->member ? ($visit->member->class_or_dept ?? '-') : ($visit->class_or_dept ?? '-') }}
                            </span>
                        </td>
                        <td class="p-4 pr-6 text-slate-600 dark:text-slate-400">
                            {{ $visit->purpose ?? 'Membaca / Belajar' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400 font-medium">Tidak ada data kunjungan yang sesuai dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($visits->hasPages())
    <div class="p-6 border-t border-slate-100 dark:border-slate-800">
        {{ $visits->links() }}
    </div>
    @endif
</div>
@endsection
