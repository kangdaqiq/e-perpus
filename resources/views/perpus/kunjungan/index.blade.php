@extends('perpus.layouts.app')

@section('title', 'Buku Tamu Kunjungan')

@section('content')
<div x-data="{ openManualModal: false }">
    <!-- Header Page Actions -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Buku Tamu</h2>
            <p class="text-sm text-slate-400">Log kehadiran pengunjung perpustakaan.</p>
        </div>
        <div>
            <button @click="openManualModal = true" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-semibold rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-150 flex items-center gap-2">
                <i class="fa-solid fa-pen"></i>
                <span>Catat Tamu Manual</span>
            </button>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl mb-6 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
        <form action="{{ route('perpus.kunjungan.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-4 items-center">
            <!-- Search field -->
            <div class="relative w-full sm:w-72">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}"
                       class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors"
                       placeholder="Cari nama atau kode anggota...">
            </div>

            <!-- Date Picker -->
            <div class="relative w-full sm:w-48">
                <input type="date" name="date" value="{{ $date }}"
                       class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors">
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-2xl text-sm font-semibold active:scale-95 transition-all">
                Filter
            </button>

            @if($search || $date != today()->format('Y-m-d'))
                <a href="{{ route('perpus.kunjungan.index') }}" class="text-xs text-rose-500 font-semibold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                        <th class="p-6">Tanggal - Jam</th>
                        <th class="p-6">Nama Pengunjung</th>
                        <th class="p-6">Kelas / Divisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                    @forelse($visits as $visit)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-6 text-slate-500 font-medium">
                                {{ $visit->scanned_at->format('d M Y') }} - <span class="font-bold text-slate-800 dark:text-slate-200">{{ $visit->scanned_at->format('H:i:s') }}</span>
                            </td>
                            <td class="p-6 font-bold text-slate-900 dark:text-slate-100">
                                {{ $visit->member->name ?? $visit->visitor_name }}
                                @if($visit->member)
                                    <div class="text-xs text-slate-400 font-medium mt-0.5">NIS/NIP: {{ $visit->member->member_code }}</div>
                                @else
                                    <div class="text-xs text-amber-500 font-medium mt-0.5">Pengunjung Luar (Tamu)</div>
                                @endif
                            </td>
                            <td class="p-6 text-slate-500">
                                {{ $visit->member->class_or_dept ?? $visit->class_or_dept ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="p-12 text-center text-slate-400 font-medium">Tidak ada log kunjungan untuk tanggal filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($visits->hasPages())
            <div class="p-6 border-t border-slate-100 dark:border-slate-800">
                {{ $visits->appends(['search' => $search, 'date' => $date])->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: ADD MANUAL VISIT -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4" 
         x-show="openManualModal" x-transition x-cloak>
        <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden" 
             @click.away="openManualModal = false"
             x-data="{ isGuest: false, memberId: '' }">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg">Catat Kunjungan Baru</h3>
                <button @click="openManualModal = false" class="text-slate-400 hover:text-slate-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('perpus.kunjungan.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <!-- Toggle Guest/Member -->
                <div class="flex bg-slate-100 dark:bg-slate-950 p-1.5 rounded-xl mb-4">
                    <button type="button" @click="isGuest = false" 
                            class="flex-1 py-2 text-xs font-bold rounded-lg transition-all"
                            :class="!isGuest ? 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                        Anggota Terdaftar
                    </button>
                    <button type="button" @click="isGuest = true; memberId = ''" 
                            class="flex-1 py-2 text-xs font-bold rounded-lg transition-all"
                            :class="isGuest ? 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 shadow-sm' : 'text-slate-400 hover:text-slate-600'">
                        Tamu / Pengunjung Luar
                    </button>
                </div>

                <!-- SELECT MEMBER -->
                <div x-show="!isGuest" x-cloak>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pilih Anggota</label>
                    <select name="member_id" x-model="memberId" :required="!isGuest" 
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm">
                        <option value="">-- Pilih Anggota --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}">{{ $member->name }} ({{ ucfirst($member->type) }} - {{ $member->class_or_dept }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- GUEST INPUT FIELDS -->
                <div x-show="isGuest" class="space-y-4" x-cloak>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Tamu</label>
                        <input type="text" name="visitor_name" :required="isGuest" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm" placeholder="Nama lengkap pengunjung...">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kelas / Divisi / Instansi</label>
                        <input type="text" name="class_or_dept" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm" placeholder="Contoh: XII RPL 1, SMPN 2, dll...">
                    </div>
                </div>



                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openManualModal = false" class="px-4 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/20">Catat Kunjungan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
