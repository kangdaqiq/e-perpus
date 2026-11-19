@extends('perpus.layouts.app')

@section('title', 'Buku Tamu Kunjungan')

@section('content')
<div x-data="kunjunganPage()" @usb-rfid-scan.window="handleScan($event.detail.uid)">
    <!-- Header Page Actions -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Buku Tamu</h2>
            <p class="text-sm text-slate-400">Log kehadiran pengunjung perpustakaan.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button @click="toggleUsbPanel()" 
                    class="px-5 py-3 font-semibold rounded-2xl transition-all duration-150 flex items-center gap-2 active:scale-95 border"
                    :class="usbPanel ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:border-emerald-500'">
                <i class="fa-brands fa-usb"></i>
                <span x-text="usbPanel ? 'Scanner USB Aktif' : 'Scan Kartu (USB)'"></span>
            </button>
            <button @click="openManualModal = true" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-semibold rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-150 flex items-center gap-2">
                <i class="fa-solid fa-pen"></i>
                <span>Catat Tamu Manual</span>
            </button>
        </div>
    </div>

    <!-- USB RFID SCANNER PANEL -->
    <div x-show="usbPanel" x-transition x-cloak
         class="bg-white dark:bg-slate-900 border border-emerald-200 dark:border-emerald-900/50 p-6 rounded-3xl mb-6 shadow-sm grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-id-card-clip" :class="loading ? 'animate-pulse' : ''"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100">Tap Kartu Kunjungan (Scanner USB)</h3>
                    <p class="text-xs text-slate-400">Tempelkan kartu RFID anggota pada scanner USB untuk mencatat kunjungan.</p>
                </div>
            </div>

            <input type="text" x-ref="usbVisitInput" data-rfid-capture autocomplete="off"
                   :disabled="loading"
                   @blur="keepFocus()"
                   placeholder="Menunggu tap kartu..."
                   class="w-full px-4 py-4 bg-emerald-50/50 dark:bg-emerald-950/20 border-2 border-dashed border-emerald-300 dark:border-emerald-800 rounded-2xl text-center text-base font-mono font-bold text-slate-800 dark:text-slate-200 focus:outline-none focus:border-emerald-600 focus:border-solid">

            <!-- Last Result -->
            <template x-if="lastResult">
                <div class="p-4 rounded-2xl border flex items-center gap-3"
                     :class="lastResult.ok ? (lastResult.duplicate ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-900/50 text-amber-700 dark:text-amber-400' : 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-900/50 text-emerald-700 dark:text-emerald-400') : 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-400'">
                    <i class="text-2xl fa-solid" :class="lastResult.ok ? (lastResult.duplicate ? 'fa-clock-rotate-left' : 'fa-circle-check') : 'fa-circle-xmark'"></i>
                    <div class="min-w-0">
                        <p class="font-bold text-sm truncate" x-text="lastResult.name || 'Kartu Tidak Dikenal'"></p>
                        <p class="text-xs" x-text="lastResult.message"></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Recent scans (this session) -->
        <div>
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Scan Terbaru (Sesi Ini)</span>
                <a href="{{ route('perpus.kunjungan.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline" x-show="recent.length > 0">
                    <i class="fa-solid fa-rotate-right mr-1"></i>Muat Ulang Tabel
                </a>
            </div>
            <div class="max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                <template x-for="(item, idx) in recent" :key="idx">
                    <div class="py-2.5 flex justify-between items-center gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 dark:text-slate-100 truncate" x-text="item.name"></p>
                            <p class="text-xs text-slate-400" x-text="item.detail"></p>
                        </div>
                        <span class="text-xs font-mono font-bold text-slate-500 flex-shrink-0" x-text="item.time"></span>
                    </div>
                </template>
                <div x-show="recent.length === 0" class="py-8 text-center text-xs text-slate-400">
                    Belum ada kartu yang di-scan.
                </div>
            </div>
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
                        <th class="p-6 text-right">Aksi</th>
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
                            <td class="p-6 text-right">
                                <form action="{{ route('perpus.kunjungan.destroy', $visit->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus data kunjungan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Kunjungan"
                                            class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 inline-flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center text-slate-400 font-medium">Tidak ada log kunjungan untuk tanggal filter ini.</td>
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

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('kunjunganPage', () => ({
        openManualModal: false,
        usbPanel: localStorage.getItem('perpus_visit_usb_panel') === '1',
        loading: false,
        lastResult: null,
        recent: [],

        init() {
            if (this.usbPanel) this.focusInput();
        },

        toggleUsbPanel() {
            this.usbPanel = !this.usbPanel;
            localStorage.setItem('perpus_visit_usb_panel', this.usbPanel ? '1' : '0');
            if (this.usbPanel) this.focusInput();
        },

        focusInput() {
            this.$nextTick(() => setTimeout(() => this.$refs.usbVisitInput && this.$refs.usbVisitInput.focus(), 50));
        },

        keepFocus() {
            // Kembalikan fokus hanya jika pengguna tidak sedang memakai input lain
            setTimeout(() => {
                if (this.usbPanel && !this.openManualModal && document.activeElement === document.body) {
                    this.focusInput();
                }
            }, 100);
        },

        handleScan(uid) {
            if (!this.usbPanel || this.openManualModal || this.loading) return;
            this.loading = true;

            fetch('{{ route('perpus.rfid.usb.visit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ uid })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    UsbRfid.beep(true);
                    this.lastResult = {
                        ok: true,
                        duplicate: data.duplicate,
                        name: data.member.name,
                        message: data.message
                    };
                    if (!data.duplicate) {
                        this.recent.unshift({
                            name: data.member.name,
                            detail: (data.member.class_or_dept || '-') + ' • ' + (data.member.code || '-'),
                            time: data.scanned_at
                        });
                    }
                } else {
                    UsbRfid.beep(false);
                    this.lastResult = { ok: false, name: '', message: data.message || 'Kartu tidak dikenali.' };
                }
                this.focusInput();
            })
            .catch(() => {
                this.loading = false;
                UsbRfid.beep(false);
                this.lastResult = { ok: false, name: '', message: 'Koneksi jaringan error.' };
                this.focusInput();
            });
        }
    }));
});
</script>
@endpush
@endsection
