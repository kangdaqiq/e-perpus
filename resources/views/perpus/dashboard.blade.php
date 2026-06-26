@extends('perpus.layouts.app')

@section('title', 'Dashboard Perpustakaan')

@section('content')
<!-- Welcome banner -->


<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    <!-- Stat 1 -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl flex items-center gap-5 shadow-sm">
        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/50 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-book"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Koleksi Buku</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($totalBooks) }}</h3>
        </div>
    </div>

    <!-- Stat 2 -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl flex items-center gap-5 shadow-sm">
        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-handshake"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Peminjaman Aktif</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($activeLoans) }}</h3>
        </div>
    </div>

    <!-- Stat 3 -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl flex items-center gap-5 shadow-sm">
        <div class="w-12 h-12 bg-rose-50 dark:bg-rose-950/50 rounded-2xl flex items-center justify-center text-rose-600 dark:text-rose-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Terlambat Kembali</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($overdueLoans) }}</h3>
        </div>
    </div>

    <!-- Stat 4 -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl flex items-center gap-5 shadow-sm">
        <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/50 rounded-2xl flex items-center justify-center text-amber-600 dark:text-amber-400 text-xl flex-shrink-0">
            <i class="fa-solid fa-users-rectangle"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pengunjung Hari Ini</p>
            <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($todayVisits) }}</h3>
        </div>
    </div>
</div>

<!-- Secondary Stats Grid (Denda & Anggota) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-5">
            <div class="w-12 h-12 bg-yellow-50 dark:bg-yellow-950/50 rounded-2xl flex items-center justify-center text-yellow-600 dark:text-yellow-400 text-xl">
                <i class="fa-solid fa-rupiah-sign"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-medium">Total Denda Terkumpul</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-1">Rp {{ number_format($totalFines, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-5">
            <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-2xl flex items-center justify-center text-purple-600 dark:text-purple-400 text-xl">
                <i class="fa-solid fa-id-card"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-medium">Total Anggota Terdaftar</p>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($totalMembers) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Peringkat & Sorotan Aktivitas -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mb-8">
    <!-- Papan Peringkat (Span 2) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/50 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg">
                    <i class="fa-solid fa-trophy text-amber-500 animate-pulse"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Anggota Teraktif</h3>
                    <p class="text-xs text-slate-400 font-medium">Peringkat keaktifan anggota berdasarkan poin (Pinjam = {{ $school->point_borrow ?? 10 }} Poin, Kunjungan = {{ $school->point_visit ?? 5 }} Poin)</p>
                </div>
            </div>
            
            <div class="flow-root mt-6">
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php 
                        $maxPoints = $topMembers->first()->points ?? 1; 
                        if ($maxPoints == 0) $maxPoints = 1;
                    @endphp
                    @forelse($topMembers as $index => $member)
                        <li class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <!-- Rank badge -->
                                <div class="flex-shrink-0">
                                    @if($index == 0)
                                        <span class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-300 to-amber-500 text-white font-bold flex items-center justify-center shadow-sm shadow-amber-500/20">1</span>
                                    @elseif($index == 1)
                                        <span class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-200 to-slate-400 text-white font-bold flex items-center justify-center shadow-sm shadow-slate-400/20">2</span>
                                    @elseif($index == 2)
                                        <span class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-600 to-amber-700 text-white font-bold flex items-center justify-center shadow-sm shadow-amber-700/20">3</span>
                                    @else
                                        <span class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold flex items-center justify-center border border-slate-200 dark:border-slate-700">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $member->name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ ucfirst($member->type) }} • {{ $member->class_or_dept }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 flex-shrink-0 w-full sm:w-auto sm:justify-end">
                                <!-- Progress bar -->
                                <div class="hidden sm:block w-32 bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                    <div class="bg-indigo-600 dark:bg-indigo-500 h-full rounded-full transition-all duration-500" style="width: {{ ($member->points / $maxPoints) * 100 }}%"></div>
                                </div>
                                <div class="flex items-center gap-2 ml-auto sm:ml-0">
                                    <span class="text-xs text-slate-400 font-medium">({{ $member->loans_count }} Pinjam, {{ $member->visits_count }} Kunjungan)</span>
                                    <span class="text-xs px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-full font-bold shadow-sm">{{ $member->points }} Poin</span>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-slate-400">Belum ada aktivitas anggota perpustakaan.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Sorotan Khusus (Span 1) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm flex flex-col justify-between">
        <div>
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                Sorotan Aktivitas
            </h3>
            
            <div class="space-y-6">
                <!-- Buku Terfavorit -->
                <div class="relative p-4 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-slate-100 dark:border-slate-800/80 transition-all duration-300 hover:scale-[1.01] hover:shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-medium">Buku Terfavorit</span>
                        <i class="fa-solid fa-fire text-amber-500"></i>
                    </div>
                    @if($favoriteBook && $favoriteBook->loans_count > 0)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-14 bg-slate-100 dark:bg-slate-800 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 flex-shrink-0 flex items-center justify-center">
                                @if($favoriteBook->cover_url)
                                    <img src="{{ $favoriteBook->cover_url }}" alt="Cover" class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-image text-slate-300 text-lg"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100 truncate text-xs">{{ $favoriteBook->title }}</h4>
                                <p class="text-[10px] text-slate-400 truncate">{{ $favoriteBook->author ?? 'Tanpa Penulis' }}</p>
                                <span class="inline-flex items-center gap-1 mt-1 text-[10px] px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-full font-semibold">
                                    <i class="fa-solid fa-handshake"></i> {{ $favoriteBook->loans_count }}x Dipinjam
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 py-1">Belum ada data peminjaman buku.</p>
                    @endif
                </div>

                <!-- Kunjungan Terbanyak -->
                <div class="relative p-4 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-slate-100 dark:border-slate-800/80 transition-all duration-300 hover:scale-[1.01] hover:shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-medium">Kunjungan Terbanyak</span>
                        <i class="fa-solid fa-door-open text-emerald-500"></i>
                    </div>
                    @if($topVisitor && $topVisitor->visits_count > 0)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold flex-shrink-0 text-sm">
                                {{ strtoupper(substr($topVisitor->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100 truncate text-xs">{{ $topVisitor->name }}</h4>
                                <p class="text-[10px] text-slate-400 truncate">{{ ucfirst($topVisitor->type) }} • {{ $topVisitor->class_or_dept }}</p>
                                <span class="inline-flex items-center gap-1 mt-1 text-[10px] px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-full font-semibold">
                                    {{ $topVisitor->visits_count }} Kunjungan ({{ $topVisitor->visits_count * ($school->point_visit ?? 5) }} Poin)
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 py-1">Belum ada data kunjungan.</p>
                    @endif
                </div>

                <!-- Peminjam Terbanyak -->
                <div class="relative p-4 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-slate-100 dark:border-slate-800/80 transition-all duration-300 hover:scale-[1.01] hover:shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-medium">Peminjam Terbanyak</span>
                        <i class="fa-solid fa-award text-purple-500"></i>
                    </div>
                    @if($topBorrower && $topBorrower->loans_count > 0)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-50 dark:bg-purple-950/40 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold flex-shrink-0 text-sm">
                                {{ strtoupper(substr($topBorrower->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100 truncate text-xs">{{ $topBorrower->name }}</h4>
                                <p class="text-[10px] text-slate-400 truncate">{{ ucfirst($topBorrower->type) }} • {{ $topBorrower->class_or_dept }}</p>
                                <span class="inline-flex items-center gap-1 mt-1 text-[10px] px-2 py-0.5 bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 rounded-full font-semibold">
                                    {{ $topBorrower->loans_count }} Peminjaman ({{ $topBorrower->loans_count * ($school->point_borrow ?? 10) }} Poin)
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 py-1">Belum ada data peminjaman.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lists Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
    <!-- Log Kunjungan Terbaru -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Kunjungan Terbaru</h3>
            <a href="{{ route('perpus.kunjungan.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i></a>
        </div>
        <div class="flow-root">
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentVisits as $visit)
                    <li class="py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                                {{ strtoupper(substr($visit->member->name ?? 'Tamu', 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $visit->member->name ?? $visit->visitor_name }}</p>
                                <p class="text-xs text-slate-400">{{ $visit->member ? ucfirst($visit->member->type) . ' • ' . $visit->member->class_or_dept : 'Tamu • ' . $visit->class_or_dept }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-500 font-medium">{{ $visit->scanned_at->format('H:i') }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-slate-400">Belum ada kunjungan hari ini.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Peminjaman Terbaru -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Transaksi Peminjaman Terbaru</h3>
            <a href="{{ route('perpus.loan.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat Semua <i class="fa-solid fa-arrow-right ml-1"></i></a>
        </div>
        <div class="flow-root">
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentLoans as $loan)
                    <li class="py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg flex-shrink-0">
                                <i class="fa-solid fa-book"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $loan->book->title }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $loan->member->name }}</p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            @if($loan->status === 'dipinjam')
                                <span class="text-xs px-2.5 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-full font-semibold">Dipinjam</span>
                            @elseif($loan->status === 'terlambat')
                                <span class="text-xs px-2.5 py-1 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-full font-semibold">Terlambat</span>
                            @else
                                <span class="text-xs px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-full font-semibold">Kembali</span>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-slate-400">Belum ada transaksi peminjaman.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
