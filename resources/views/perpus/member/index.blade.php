@extends('perpus.layouts.app')

@section('title', 'Data Siswa & Guru')

@section('content')
<div>
    <!-- Header Page Actions -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Data Siswa & Guru</h2>
            <p class="text-sm text-slate-400">Kelola informasi anggota perpustakaan dan pendaftaran kartu RFID.</p>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm flex items-center gap-5 hover:shadow-md transition-all duration-200">
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/40 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Anggota</span>
                <span class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($stats['total']) }}</span>
            </div>
        </div>

        <!-- Card 2: Students -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm flex items-center gap-5 hover:shadow-md transition-all duration-200">
            <div class="w-12 h-12 bg-sky-50 dark:bg-sky-950/40 rounded-2xl flex items-center justify-center text-sky-600 dark:text-sky-400 text-xl">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div>
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Siswa</span>
                <span class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($stats['students']) }}</span>
            </div>
        </div>

        <!-- Card 3: Teachers -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm flex items-center gap-5 hover:shadow-md transition-all duration-200">
            <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/40 rounded-2xl flex items-center justify-center text-purple-600 dark:text-purple-400 text-xl">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div>
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Guru / Staf</span>
                <span class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($stats['teachers']) }}</span>
            </div>
        </div>

        <!-- Card 4: RFID Configured -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm flex items-center gap-5 hover:shadow-md transition-all duration-200">
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xl">
                <i class="fa-solid fa-id-card-clip"></i>
            </div>
            <div>
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Kartu RFID Aktif</span>
                <span class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($stats['registered_rfid']) }}</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Panel -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl mb-6 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
        <form action="{{ route('perpus.member.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-4 items-center">
            <!-- Search input -->
            <div class="relative w-full sm:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Cari nama, NIS, NIP atau UID..." 
                       class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 font-semibold text-slate-800 dark:text-slate-200">
            </div>

            <!-- Tipe filter -->
            <div class="w-full sm:w-48">
                <select name="type" onchange="this.form.submit()" 
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 font-semibold text-slate-800 dark:text-slate-200">
                    <option value="">Semua Tipe</option>
                    <option value="siswa" {{ $type === 'siswa' ? 'selected' : '' }}>Siswa</option>
                    <option value="guru" {{ $type === 'guru' ? 'selected' : '' }}>Guru / Staf</option>
                </select>
            </div>

            @if($search || $type)
                <a href="{{ route('perpus.member.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">Reset Filter</a>
            @endif
        </form>
    </div>

    <!-- Alert Success / Error -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-2xl flex items-center gap-3 text-emerald-700 dark:text-emerald-400">
            <i class="fa-solid fa-circle-check text-xl"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-2xl flex items-center gap-3 text-rose-700 dark:text-rose-400">
            <i class="fa-solid fa-circle-xmark text-xl"></i>
            <span class="font-medium text-sm">{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-950/20">
                        <th class="p-5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">No</th>
                        <th class="p-5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Nama Anggota</th>
                        <th class="p-5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Tipe</th>
                        <th class="p-5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">NIS / NIP</th>
                        <th class="p-5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Kelas / Divisi</th>
                        <th class="p-5 text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">UID Kartu RFID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-sm">
                    @forelse($members as $index => $member)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/10 transition-colors">
                            <td class="p-5 text-slate-400 dark:text-slate-500 font-semibold">{{ $members->firstItem() + $index }}</td>
                            <td class="p-5">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $member->name }}</div>
                            </td>
                            <td class="p-5">
                                @if($member->source_type === 'siswa')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400">
                                        Siswa
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 text-xs rounded-full font-bold uppercase tracking-wider bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400">
                                        Guru
                                    </span>
                                @endif
                            </td>
                            <td class="p-5">
                                <code class="px-2 py-1 bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $member->member_code }}
                                </code>
                            </td>
                            <td class="p-5 font-semibold text-slate-600 dark:text-slate-400">
                                {{ $member->class_or_dept }}
                            </td>
                            <td class="p-5">
                                @if($member->rfid_uid)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 rounded-xl text-emerald-600 dark:text-emerald-400 font-mono text-xs font-bold">
                                        <i class="fa-solid fa-id-card-clip text-xs"></i>
                                        <span>{{ $member->rfid_uid }}</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500 italic">Belum terhubung</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400 font-medium">Belum ada data anggota yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($members->hasPages())
            <div class="p-6 border-t border-slate-100 dark:border-slate-800">
                {{ $members->appends(['search' => $search, 'type' => $type])->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
