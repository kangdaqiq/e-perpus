@extends('perpus.layouts.app')

@section('title', 'Daftar Peminjaman Buku')

@section('content')
<div x-data="loanTransactions()">
    <!-- Header Page Actions -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Daftar Peminjaman Buku</h2>
            <p class="text-sm text-slate-400">Kelola peminjaman aktif, keterlambatan pengembalian, dan denda.</p>
        </div>
        <div>
            <button @click="openReturnScanModal = true; resetReturnScanForm()" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-semibold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all duration-150 flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>Kembalikan Buku</span>
            </button>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl mb-6 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
        <form action="{{ route('perpus.loan.index') }}" method="GET" class="w-full flex flex-col sm:flex-row gap-4 items-center">
            <!-- Search field -->
            <div class="relative w-full sm:w-80">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" value="{{ $search }}"
                       class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors"
                       placeholder="Cari anggota atau judul buku...">
            </div>

            <!-- Status filter -->
            <div class="relative w-full sm:w-48">
                <select name="status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600">
                    <option value="">Semua Status</option>
                    <option value="dipinjam" {{ $status == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="kembali" {{ $status == 'kembali' ? 'selected' : '' }}>Dikembalikan</option>
                </select>
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-2xl text-sm font-semibold active:scale-95 transition-all">
                Filter
            </button>

            @if($search || $status)
                <a href="{{ route('perpus.loan.index') }}" class="text-xs text-rose-500 font-semibold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Bersihkan Filter
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
                        <th class="p-6">Peminjam</th>
                        <th class="p-6">Buku</th>
                        <th class="p-6">Tgl Pinjam</th>
                        <th class="p-6">Batas Kembali</th>
                        <th class="p-6">Tgl Kembali</th>
                        <th class="p-6">Status</th>
                        <th class="p-6">Denda</th>
                        <th class="p-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                    @forelse($loans as $loan)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-6">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $loan->member->name }}</div>
                                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-0.5">{{ $loan->member->type }} • {{ $loan->member->class_or_dept }}</div>
                            </td>
                            <td class="p-6 max-w-xs">
                                <div class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $loan->book->title }}</div>
                                <div class="text-xs text-slate-400">ISBN: {{ $loan->book->code }} • Qty: <span class="font-bold text-slate-700 dark:text-slate-350">{{ $loan->qty ?? 1 }}</span></div>
                            </td>
                            <td class="p-6 text-slate-500 font-medium">{{ $loan->borrow_date->format('d M Y') }}</td>
                            <td class="p-6 text-slate-500 font-medium">{{ $loan->due_date->format('d M Y') }}</td>
                            <td class="p-6 text-slate-500 font-medium">
                                {{ $loan->return_date ? $loan->return_date->format('d M Y') : '-' }}
                            </td>
                            <td class="p-6">
                                @if($loan->status === 'dipinjam')
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 text-xs rounded-full font-bold uppercase tracking-wider">
                                        Dipinjam
                                    </span>
                                @elseif($loan->status === 'terlambat')
                                    <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 text-xs rounded-full font-bold uppercase tracking-wider animate-pulse">
                                        Terlambat
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 text-xs rounded-full font-bold uppercase tracking-wider">
                                        Kembali
                                    </span>
                                @endif
                            </td>
                            <td class="p-6 font-bold text-slate-900 dark:text-slate-100">
                                @if($loan->fine > 0)
                                    <span class="text-rose-600 dark:text-rose-400">Rp {{ number_format($loan->fine, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-6 text-right">
                                @if($loan->status === 'dipinjam' || $loan->status === 'terlambat')
                                    <button type="button" 
                                            @click="triggerReturn({ 
                                                id: {{ $loan->id }}, 
                                                member_name: '{{ addslashes($loan->member->name) }}', 
                                                book_title: '{{ addslashes($loan->book->title) }}', 
                                                borrow_date: '{{ $loan->borrow_date->format('Y-m-d') }}', 
                                                due_date: '{{ $loan->due_date->format('Y-m-d') }}',
                                                qty: {{ $loan->qty ?? 1 }}
                                            })" 
                                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/10 transition-all flex items-center gap-1.5 ml-auto">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>Kembalikan</span>
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 italic">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-400 font-medium">Belum ada transaksi peminjaman buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($loans->hasPages())
            <div class="p-6 border-t border-slate-100 dark:border-slate-800">
                {{ $loans->appends(['search' => $search, 'status' => $status])->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: KEMBALIKAN CEPAT (LOOKUP SCAN RFID) -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4" 
         x-show="openReturnScanModal" x-transition x-cloak>
        <div class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-visible shadow-emerald-500/10" 
             @click.away="closeReturnScanModal()">
            
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-arrows-rotate text-emerald-600"></i>
                    <span x-show="returnScanStep === 'select_device'">Pilih Scanner Pengembalian</span>
                    <span x-show="returnScanStep === 'waiting'">Menunggu Tempel Kartu Anggota</span>
                    <span x-show="returnScanStep === 'loan_list'">Daftar Buku Dipinjam</span>
                    <span x-show="returnScanStep === 'failed'">Pencarian Gagal</span>
                </h3>
                <button @click="closeReturnScanModal()" class="text-slate-400 hover:text-slate-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- STEP 1: SELECT DEVICE -->
            <div class="p-6 space-y-6" x-show="returnScanStep === 'select_device'">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Perangkat Scanner RFID</label>
                    <select x-model="returnScanDeviceId" 
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-emerald-600">
                        <option value="">-- Pilih Scanner --</option>
                        @foreach($devices as $dev)
                            <option value="{{ $dev->id }}">{{ $dev->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Pilih perangkat scanner meja pustakawan yang aktif untuk membaca kartu RFID anggota.</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="closeReturnScanModal()" 
                            class="px-5 py-2.5 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-bold rounded-2xl text-xs hover:bg-slate-100 dark:hover:bg-slate-800">
                        Batal
                    </button>
                    <button type="button" @click="startReturnScan()" 
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs shadow-lg shadow-emerald-600/20 flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card-clip"></i>
                        <span>Mulai Scan Kartu</span>
                    </button>
                </div>
            </div>

            <!-- STEP 2: WAITING FOR CARD TAP -->
            <div class="p-8 text-center space-y-6" x-show="returnScanStep === 'waiting'">
                <div class="relative w-24 h-24 mx-auto flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/40 rounded-full text-emerald-600 dark:text-emerald-400 text-3xl animate-pulse">
                    <div class="absolute inset-0 rounded-full border border-emerald-600/30 animate-ping"></div>
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>

                <div>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100">MENUNGGU TEMPEL KARTU</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Tempelkan kartu RFID siswa atau guru pada mesin scanner yang dipilih.
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 rounded-xl text-amber-700 dark:text-amber-400 font-bold text-xs">
                    <i class="fa-solid fa-stopwatch animate-spin-slow"></i>
                    <span>Sesi Berakhir dalam <span x-text="returnScanExpiresIn"></span> Detik</span>
                </div>

                <div class="pt-4 flex justify-center border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="resetReturnScanForm()" class="px-5 py-2.5 border border-slate-200 dark:border-slate-800 text-rose-500 font-bold rounded-2xl text-xs hover:bg-rose-50 dark:hover:bg-rose-950/20">
                        Batalkan Scan
                    </button>
                </div>
            </div>

            <!-- STEP 3: LOAN LIST -->
            <div class="p-6 space-y-5" x-show="returnScanStep === 'loan_list'">
                <!-- Member Summary -->
                <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100/50 dark:border-emerald-900/30 rounded-2xl flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200" x-text="returnScanMemberName"></h4>
                        <p class="text-xs text-slate-400">Daftar buku yang sedang dipinjam aktif.</p>
                    </div>
                </div>

                <!-- Loans List Container -->
                <div class="max-h-64 overflow-y-auto space-y-3 pr-1">
                    <template x-for="loan in returnScanLoans" :key="loan.id">
                        <div class="p-4 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-2xl flex justify-between items-center gap-4 transition-all hover:border-slate-200 dark:hover:border-slate-800">
                            <div class="min-w-0">
                                <h5 class="font-bold text-slate-800 dark:text-slate-100 text-xs truncate" x-text="loan.book_title + ' (Qty: ' + (loan.qty || 1) + ')'"></h5>
                                <p class="text-[10px] text-slate-400 mt-1">
                                    Pinjam: <span x-text="formatDate(loan.borrow_date)"></span> • Batas: <span x-text="formatDate(loan.due_date)" class="text-rose-500 font-semibold"></span>
                                </p>
                            </div>
                            <button type="button" 
                                    @click="closeReturnScanModal(); triggerReturn(loan)"
                                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[10px] flex items-center gap-1 flex-shrink-0 transition-all active:scale-95">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Kembalikan</span>
                            </button>
                        </div>
                    </template>

                    <template x-if="returnScanLoans.length === 0">
                        <div class="py-8 text-center text-xs text-slate-400">
                            <i class="fa-solid fa-circle-info text-slate-300 text-lg block mb-2"></i>
                            Anggota tidak memiliki pinjaman buku yang aktif.
                        </div>
                    </template>
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="resetReturnScanForm()" 
                            class="px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white font-bold rounded-2xl text-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Scan Kartu Lain</span>
                    </button>
                </div>
            </div>

            <!-- STEP 4: SCAN FAILED -->
            <div class="p-8 text-center space-y-6" x-show="returnScanStep === 'failed'">
                <div class="w-20 h-20 mx-auto flex items-center justify-center bg-rose-50 dark:bg-rose-950/40 rounded-full text-rose-600 dark:text-rose-400 text-4xl">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>

                <div>
                    <h3 class="font-bold text-xl text-slate-900 dark:text-slate-100">PENCARIAN GAGAL</h3>
                    <p class="text-sm text-rose-500 mt-1 font-bold" x-text="returnScanErrorMessage"></p>
                </div>

                <div class="pt-4 flex gap-3 justify-center border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="resetReturnScanForm()" class="px-5 py-2 border border-slate-200 dark:border-slate-800 font-bold rounded-2xl text-xs hover:bg-slate-50 dark:hover:bg-rose-950/20">
                        Kembali / Batal
                    </button>
                    <button type="button" @click="startReturnScan()"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs shadow-lg shadow-emerald-600/20">
                        Coba Ulang Scan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: PENGEMBALIAN BUKU & DENDA -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4" 
         x-show="openReturnModal" x-transition x-cloak>
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-emerald-500/5"
             @click.away="openReturnModal = false">
            
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>Form Pengembalian Buku</span>
                </h3>
                <button @click="openReturnModal = false" class="text-slate-400 hover:text-slate-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Form & Content -->
            <form :action="'{{ url('/loans') }}/' + selectedLoan.id + '/return'" method="POST">
                @csrf
                <input type="hidden" name="return_date" :value="returnDate">

                <div class="p-6 space-y-5">
                    <!-- Book & Member Details Card -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-2xl space-y-3">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Buku</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="selectedLoan.book_title + ' (Qty: ' + (selectedLoan.qty || 1) + ')'"></span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Peminjam</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="selectedLoan.member_name"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t border-slate-150 dark:border-slate-850 pt-2.5">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tgl Pinjam</span>
                                <span class="text-xs text-slate-600 dark:text-slate-300 font-semibold" x-text="formatDate(selectedLoan.borrow_date)"></span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Batas Kembali</span>
                                <span class="text-xs text-rose-500 font-semibold" x-text="formatDate(selectedLoan.due_date)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Input return_date -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Pengembalian</label>
                        <input type="date" x-model="returnDate" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors">
                    </div>

                    <!-- Input return_qty -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Jumlah Buku yang Dikembalikan</label>
                        <input type="number" name="return_qty" x-model="returnQty" min="1" :max="selectedLoan.qty" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors">
                        <p class="text-[10px] text-slate-400 mt-1">Maksimal pengembalian: <span class="font-bold text-slate-600 dark:text-slate-350" x-text="selectedLoan.qty"></span> buku.</p>
                    </div>

                    <!-- Fine Calculation Preview Box -->
                    <div class="mt-2">
                        <!-- Overdue Fine -->
                        <div x-show="lateFeeInfo.fine > 0" x-transition
                             class="p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50 rounded-2xl flex items-center gap-3.5 text-rose-700 dark:text-rose-450">
                            <i class="fa-solid fa-triangle-exclamation text-xl animate-bounce"></i>
                            <div>
                                <p class="text-xs font-bold">Terlambat <span x-text="lateFeeInfo.days" class="underline"></span> Hari</p>
                                <p class="text-sm font-black mt-0.5">Denda: Rp <span x-text="new Intl.NumberFormat('id-ID').format(lateFeeInfo.fine)"></span></p>
                            </div>
                        </div>

                        <!-- No Fine -->
                        <div x-show="lateFeeInfo.fine === 0" x-transition
                             class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl flex items-center gap-3 text-emerald-700 dark:text-emerald-400 text-xs font-semibold">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                            <span>Buku dikembalikan tepat waktu. Tidak ada denda.</span>
                        </div>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="p-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="openReturnModal = false"
                            class="px-5 py-2.5 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-bold rounded-2xl text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl text-xs shadow-lg shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Konfirmasi Kembali</span>
                    </button>
                </div>
            </form>
          </div>
      </div>
  </div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('loanTransactions', () => ({
        openReturnModal: false,
        selectedLoan: { id: null, member_name: '', book_title: '', borrow_date: '', due_date: '', qty: 1 },
        returnDate: new Date().toISOString().split('T')[0],
        returnQty: 1,

        // Return Scan (RFID Quick Return)
        openReturnScanModal: false,
        returnScanStep: 'select_device', // 'select_device', 'waiting', 'loan_list', 'failed'
        returnScanDeviceId: '',
        returnScanPendingId: null,
        returnScanExpiresIn: 120,
        returnScanCountdownInterval: null,
        returnScanPollingInterval: null,
        returnScanMemberName: '',
        returnScanLoans: [],
        returnScanErrorMessage: '',

        triggerReturn(loan) {
            this.selectedLoan = { ...loan };
            this.returnDate = new Date().toISOString().split('T')[0];
            this.returnQty = loan.qty || 1;
            this.openReturnModal = true;
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        get lateFeeInfo() {
            if (!this.selectedLoan || !this.selectedLoan.due_date || !this.returnDate) {
                return { days: 0, fine: 0 };
            }
            const returnD = new Date(this.returnDate + 'T00:00:00');
            const dueD = new Date(this.selectedLoan.due_date + 'T00:00:00');
            const diffTime = returnD - dueD;
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            if (diffDays > 0) {
                return {
                    days: diffDays,
                    fine: diffDays * 1000
                };
            }
            return { days: 0, fine: 0 };
        },

        resetReturnScanForm() {
            this.stopReturnScanTimers();
            this.returnScanStep = 'select_device';
            this.returnScanPendingId = null;
            this.returnScanErrorMessage = '';
            this.returnScanMemberName = '';
            this.returnScanLoans = [];
        },

        closeReturnScanModal() {
            this.stopReturnScanTimers();
            this.openReturnScanModal = false;
        },

        startReturnScan() {
            if (!this.returnScanDeviceId) {
                alert('Silakan pilih perangkat scanner RFID.');
                return;
            }
            this.returnScanStep = 'waiting';
            this.returnScanExpiresIn = 120;
            this.returnScanErrorMessage = '';
            this.returnScanLoans = [];

            fetch('{{ route('perpus.loan.start-return-verification') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    device_id: this.returnScanDeviceId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.returnScanPendingId = data.pending_id;
                    this.returnScanExpiresIn = data.expires_in;
                    
                    this.returnScanCountdownInterval = setInterval(() => {
                        if (this.returnScanExpiresIn > 0) {
                            this.returnScanExpiresIn--;
                        } else {
                            this.stopReturnScanTimers();
                            this.returnScanStep = 'failed';
                            this.returnScanErrorMessage = 'Waktu tempel kartu habis (Timeout).';
                        }
                    }, 1000);

                    this.returnScanPollingInterval = setInterval(() => {
                        this.checkReturnScanStatus();
                    }, 2000);
                } else {
                    this.returnScanStep = 'select_device';
                    alert(data.message || 'Terjadi kesalahan sistem.');
                }
            })
            .catch(err => {
                this.returnScanStep = 'select_device';
                alert('Koneksi jaringan error.');
            });
        },

        stopReturnScanTimers() {
            if (this.returnScanCountdownInterval) clearInterval(this.returnScanCountdownInterval);
            if (this.returnScanPollingInterval) clearInterval(this.returnScanPollingInterval);
        },

        checkReturnScanStatus() {
            if (!this.returnScanPendingId) return;

            fetch(`/loans/check-return-status/${this.returnScanPendingId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'verified') {
                    this.stopReturnScanTimers();
                    this.returnScanMemberName = data.member_name;
                    this.returnScanLoans = data.loans;
                    this.returnScanStep = 'loan_list';
                } else if (data.status === 'failed') {
                    this.stopReturnScanTimers();
                    this.returnScanErrorMessage = data.message;
                    this.returnScanStep = 'failed';
                } else if (data.status === 'expired') {
                    this.stopReturnScanTimers();
                    this.returnScanStep = 'failed';
                    this.returnScanErrorMessage = data.message || 'Sesi verifikasi kedaluwarsa.';
                }
            })
            .catch(err => {
                console.error('Polling error:', err);
            });
        }
    }));
});
</script>
@endpush
@endsection
