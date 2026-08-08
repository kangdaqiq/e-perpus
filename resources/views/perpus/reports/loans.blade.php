@extends('perpus.layouts.app')

@section('title', 'Rekap Peminjaman Buku')

@section('content')
<!-- Header Banner -->
<div class="mb-8 p-6 md:p-8 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-3xl text-white shadow-xl shadow-indigo-600/20 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2.5 bg-white/10 rounded-2xl backdrop-blur-md">
                <i class="fa-solid fa-file-invoice text-2xl"></i>
            </div>
            <h2 class="text-2xl md:text-3xl font-bold">Rekap Peminjaman Buku</h2>
        </div>
        <p class="text-indigo-100 text-sm md:text-base font-medium max-w-2xl">
            Laporan dan rekapitulasi data sirkulasi peminjaman buku {{ $school->name ?? '' }}. Saring berdasarkan tanggal, status peminjaman, kelas, dan jurusan.
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('perpus.reports.loans.print', request()->query()) }}" target="_blank" class="px-5 py-3 bg-white text-indigo-700 font-bold rounded-2xl shadow-lg hover:bg-indigo-50 active:scale-95 transition-all duration-200 flex items-center gap-2 text-sm">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Laporan</span>
        </a>
    </div>
</div>

<!-- Filter Box -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 mb-8 shadow-sm">
    <form method="GET" action="{{ route('perpus.reports.loans') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
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

        <!-- Filter Status -->
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Status</label>
            <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">-- Semua Status --</option>
                <option value="dipinjam" {{ $status === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="kembali" {{ $status === 'kembali' ? 'selected' : '' }}>Kembali</option>
                <option value="terlambat" {{ $status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
            </select>
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
        <div class="flex items-end gap-2">
            <div class="flex-grow">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Cari Peminjam/Buku</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama/Judul..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition-all duration-150 shadow-sm flex items-center justify-center">
                <i class="fa-solid fa-filter"></i>
            </button>
            <a href="{{ route('perpus.reports.loans') }}" class="px-3 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold rounded-xl text-sm transition-all duration-150 flex items-center justify-center" title="Reset Filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6 mb-8">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/50 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-handshake"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Transaksi</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($totalTransactions) }} <span class="text-xs text-slate-400 font-normal">({{ number_format($totalQty) }} eksemplar)</span></h3>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-950/50 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Dipinjam</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($borrowedCount) }}</h3>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kembali</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($returnedCount) }}</h3>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/50 rounded-2xl flex items-center justify-center text-rose-600 dark:text-rose-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Terlambat</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-0.5">{{ number_format($overdueCount) }}</h3>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-3xl flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/50 rounded-2xl flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-coins"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Denda</p>
            <h3 class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-0.5">Rp {{ number_format($totalFines, 0, ',', '.') }}</h3>
        </div>
    </div>
</div>

<!-- Table Rekap Peminjaman -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <div>
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Daftar Rekapitulasi Peminjaman</h3>
            <p class="text-xs text-slate-400 mt-1">Periode {{ \Carbon\Carbon::parse($startDate)->isoFormat('DD MMMM YYYY') }} s/d {{ \Carbon\Carbon::parse($endDate)->isoFormat('DD MMMM YYYY') }}</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                    <th class="p-4 pl-6">No</th>
                    <th class="p-4">Tgl Pinjam</th>
                    <th class="p-4">Tgl Jatuh Tempo</th>
                    <th class="p-4">Tgl Kembali</th>
                    <th class="p-4">Nama Peminjam</th>
                    <th class="p-4">Kelas / Jurusan</th>
                    <th class="p-4">Judul Buku</th>
                    <th class="p-4 text-center">Qty</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 pr-6 text-right">Denda</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                @forelse($loans as $index => $loan)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="p-4 pl-6 text-slate-400 font-semibold">{{ $loans->firstItem() + $index }}</td>
                        <td class="p-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                            {{ \Carbon\Carbon::parse($loan->borrow_date)->isoFormat('DD/MM/YYYY') }}
                        </td>
                        <td class="p-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                            {{ \Carbon\Carbon::parse($loan->due_date)->isoFormat('DD/MM/YYYY') }}
                        </td>
                        <td class="p-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                            {{ $loan->return_date ? \Carbon\Carbon::parse($loan->return_date)->isoFormat('DD/MM/YYYY') : '-' }}
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ $loan->member->name ?? '-' }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $loan->member->member_code ?? '-' }}</div>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs rounded-lg font-semibold">
                                {{ $loan->member->class_or_dept ?? '-' }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $loan->book->title ?? '-' }}</div>
                            <div class="text-xs text-slate-400 font-mono">{{ $loan->book->code ?? '' }}</div>
                        </td>
                        <td class="p-4 text-center font-bold text-slate-800 dark:text-slate-200">{{ $loan->qty ?? 1 }}</td>
                        <td class="p-4 text-center">
                            @if($loan->status === 'dipinjam')
                                <span class="px-2.5 py-1 bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 text-xs rounded-full font-bold">Dipinjam</span>
                            @elseif($loan->status === 'kembali')
                                <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs rounded-full font-bold">Kembali</span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-xs rounded-full font-bold">Terlambat</span>
                            @endif
                        </td>
                        <td class="p-4 pr-6 text-right font-semibold {{ $loan->fine > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-400' }}">
                            {{ $loan->fine > 0 ? 'Rp ' . number_format($loan->fine, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="p-12 text-center text-slate-400 font-medium">Tidak ada data peminjaman yang sesuai dengan filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($loans->hasPages())
    <div class="p-6 border-t border-slate-100 dark:border-slate-800">
        {{ $loans->links() }}
    </div>
    @endif
</div>
@endsection
