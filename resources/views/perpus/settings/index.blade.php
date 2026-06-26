@extends('perpus.layouts.app')

@section('title', 'Pengaturan Perpustakaan')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header Page Actions -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Pengaturan Perpustakaan</h2>
        <p class="text-sm text-slate-400">Atur parameter poin keaktifan anggota dan tarif denda keterlambatan buku.</p>
    </div>

    <!-- Settings Card Form -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/50 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg">
                <i class="fa-solid fa-gears"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100">Konfigurasi Parameter</h3>
                <p class="text-xs text-slate-400">Pengaturan khusus untuk {{ auth()->user()->school->name ?? 'sekolah Anda' }}.</p>
            </div>
        </div>

        <form action="{{ route('perpus.settings.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <!-- Section 1: Poin Keaktifan -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    Poin Papan Peringkat (Keaktifan)
                </h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-350">Poin Peminjaman Buku</label>
                        <input type="number" name="point_borrow" value="{{ old('point_borrow', $school->point_borrow ?? 10) }}" min="0" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors font-semibold text-slate-800 dark:text-slate-200">
                        <p class="text-[11px] text-slate-400">Jumlah poin yang didapatkan anggota untuk setiap transaksi peminjaman buku.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-350">Poin Kunjungan (Buku Tamu)</label>
                        <input type="number" name="point_visit" value="{{ old('point_visit', $school->point_visit ?? 5) }}" min="0" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors font-semibold text-slate-800 dark:text-slate-200">
                        <p class="text-[11px] text-slate-400">Jumlah poin yang didapatkan anggota ketika melakukan pencatatan kunjungan di buku tamu.</p>
                    </div>
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-800/80">

            <!-- Section 2: Denda Keterlambatan -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-rupiah-sign text-emerald-500"></i>
                    Denda Keterlambatan
                </h4>
                
                <div class="space-y-2 max-w-md">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-350">Tarif Denda Harian (Rupiah)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rp</span>
                        <input type="number" name="fine_per_day" value="{{ old('fine_per_day', intval($school->fine_per_day ?? 1000)) }}" min="0" required
                               class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors font-semibold text-slate-800 dark:text-slate-200">
                    </div>
                    <p class="text-[11px] text-slate-400">Besaran denda keterlambatan pengembalian buku per hari keterlambatan per transaksi.</p>
                </div>
            </div>

            <!-- Footer / Save Button -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" 
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-150 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
