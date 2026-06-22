@extends('perpus.layouts.app')

@section('title', 'Katalog Buku')

@section('content')
<div x-data="bukuCatalog()">
    <!-- Header Page Actions -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Katalog Buku Perpustakaan</h2>
            <p class="text-sm text-slate-400">Daftar buku tamu, stok, dan pencarian pustaka.</p>
        </div>
        <div>
            <button @click="openAddModal = true" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-semibold rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-150 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Buku Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter & Search Panel -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-3xl mb-6 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
        <form action="{{ route('perpus.buku.index') }}" method="GET" class="w-full md:w-96 relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" name="search" value="{{ $search }}"
                   class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors"
                   placeholder="Cari judul, ISBN, atau penulis...">
        </form>
        @if($search)
            <a href="{{ route('perpus.buku.index') }}" class="text-xs text-rose-500 font-semibold hover:underline flex items-center gap-1">
                <i class="fa-solid fa-xmark"></i> Bersihkan Pencarian
            </a>
        @endif
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                        <th class="p-6">Cover</th>
                        <th class="p-6">Kode / ISBN</th>
                        <th class="p-6">Judul</th>
                        <th class="p-6">Penulis & Penerbit</th>
                        <th class="p-6">Stok (Tersedia)</th>
                        <th class="p-6">Lokasi</th>
                        <th class="p-6 text-center">Pinjam Buku</th>
                        <th class="p-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                    @forelse($books as $book)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-6">
                                <div class="w-12 h-16 bg-slate-100 dark:bg-slate-800 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                                    @if($book->cover_url)
                                        <img src="{{ $book->cover_url }}" alt="Cover" class="w-full h-full object-cover">
                                    @else
                                        <i class="fa-solid fa-image text-slate-300 text-lg"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="p-6 font-semibold text-slate-800 dark:text-slate-100">{{ $book->code }}</td>
                            <td class="p-6 max-w-xs">
                                <div class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ $book->title }}</div>
                                <div class="text-xs text-slate-400 font-medium">Tahun: {{ $book->year ?? '-' }}</div>
                            </td>
                            <td class="p-6">
                                <div>{{ $book->author ?? 'Tanpa Penulis' }}</div>
                                <div class="text-xs text-slate-400 font-medium">{{ $book->publisher ?? 'Tanpa Penerbit' }}</div>
                            </td>
                            <td class="p-6">
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $book->stock }}</span>
                                <span class="text-xs font-semibold text-slate-400">({{ $book->sisa_stok }} sisa)</span>
                            </td>
                            <td class="p-6">
                                <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 text-xs rounded-full font-bold uppercase tracking-wider">
                                    {{ $book->location ?? 'Belum Diatur' }}
                                </span>
                            </td>
                            <td class="p-6 text-center">
                                @if($book->sisa_stok > 0)
                                    <button @click="triggerLoan({{ $book }})" 
                                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/10 transition-all flex items-center justify-center gap-1.5 mx-auto">
                                        <i class="fa-solid fa-handshake"></i>
                                        <span>Pinjam</span>
                                    </button>
                                @else
                                    <span class="text-xs px-2.5 py-1 bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-full font-bold">Stok Habis</span>
                                @endif
                            </td>
                            <td class="p-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button @click="triggerEdit({{ $book }})" 
                                            class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/20 flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button @click="triggerDelete({{ $book->id }}, '{{ addslashes($book->title) }}')" 
                                            class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-400 font-medium">Katalog buku kosong. Silakan tambahkan buku baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($books->hasPages())
            <div class="p-6 border-t border-slate-100 dark:border-slate-800">
                {{ $books->appends(['search' => $search])->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: ADD BOOK -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4" 
         x-show="openAddModal" x-transition x-cloak>
        <div class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden" 
             @click.away="openAddModal = false">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg">Tambah Buku Baru</h3>
                <button @click="openAddModal = false" class="text-slate-400 hover:text-slate-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('perpus.buku.store') }}" method="POST" enctype="multipart/form-tambah" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kode Buku / ISBN</label>
                        <input type="text" name="code" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Judul Buku</label>
                        <input type="text" name="title" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Penulis</label>
                        <input type="text" name="author" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Penerbit</label>
                        <input type="text" name="publisher" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tahun Terbit</label>
                        <input type="number" name="year" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Stok Awal</label>
                        <input type="number" name="stock" min="1" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Lokasi Rak</label>
                        <input type="text" name="location" placeholder="R-01" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Cover Image (Opsional)</label>
                    <input type="file" name="cover" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/20">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT BOOK -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4" 
         x-show="openEditModal" x-transition x-cloak>
        <div class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden" 
             @click.away="openEditModal = false">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg">Edit Buku</h3>
                <button @click="openEditModal = false" class="text-slate-400 hover:text-slate-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="`/books/${editBook.id}`" method="POST" enctype="multipart/form-edit" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kode Buku / ISBN</label>
                        <input type="text" name="code" required x-model="editBook.code" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Judul Buku</label>
                        <input type="text" name="title" required x-model="editBook.title" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Penulis</label>
                        <input type="text" name="author" x-model="editBook.author" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Penerbit</label>
                        <input type="text" name="publisher" x-model="editBook.publisher" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tahun Terbit</label>
                        <input type="number" name="year" x-model="editBook.year" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Total Stok</label>
                        <input type="number" name="stock" min="0" required x-model="editBook.stock" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Lokasi Rak</label>
                        <input type="text" name="location" x-model="editBook.location" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Ubah Cover Image (Opsional)</label>
                    <input type="file" name="cover" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/20">Perbarui Buku</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: PINJAM BUKU (TAP RFID & MANUAL SEARCH) -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4" 
         x-show="openLoanModal" x-transition x-cloak>
        <div class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-visible shadow-indigo-500/10" 
             @click.away="closeLoanModal()">
            
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-handshake text-indigo-600"></i>
                    <span x-show="step === 'input'">Proses Pinjam Buku</span>
                    <span x-show="step === 'waiting'">Memproses Peminjaman</span>
                    <span x-show="step === 'success'">Peminjaman Berhasil</span>
                    <span x-show="step === 'failed'">Peminjaman Gagal</span>
                </h3>
                <button @click="closeLoanModal()" class="text-slate-400 hover:text-slate-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Content: STEP 1 (INPUT) -->
            <div class="p-6 space-y-6" x-show="step === 'input'">
                <!-- Selected Book Info Card -->
                <div class="p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl flex items-center gap-4">
                    <div class="w-10 h-14 bg-slate-100 dark:bg-slate-800 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0">
                        <template x-if="loanBook.cover_url">
                            <img :src="loanBook.cover_url" alt="Cover" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!loanBook.cover_url">
                            <i class="fa-solid fa-image text-slate-300"></i>
                        </template>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100" x-text="loanBook.title"></h4>
                        <p class="text-xs text-slate-400 mt-0.5">Kode/ISBN: <span class="font-semibold" x-text="loanBook.code"></span></p>
                    </div>
                </div>

                <!-- Input Mode Toggle -->
                <div class="flex bg-slate-100 dark:bg-slate-950 p-1.5 rounded-2xl">
                    <button type="button" @click="inputMode = 'rfid'" 
                            class="flex-1 py-2.5 text-xs font-bold rounded-xl transition-all"
                            :class="inputMode === 'rfid' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                        <i class="fa-solid fa-id-card-clip mr-1.5"></i> Scan Kartu RFID
                    </button>
                    <button type="button" @click="inputMode = 'manual'" 
                            class="flex-1 py-2.5 text-xs font-bold rounded-xl transition-all"
                            :class="inputMode === 'manual' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300'">
                        <i class="fa-solid fa-keyboard mr-1.5"></i> Input Manual (RFID Rusak)
                    </button>
                </div>

                <!-- RFID MODE OPTIONS -->
                <div x-show="inputMode === 'rfid'" class="space-y-4">
                    <p class="text-xs text-slate-400">Pilih perangkat scanner RFID peminjaman di meja Anda:</p>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pilih Perangkat RFID</label>
                        <select x-model="deviceId" required
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 font-semibold text-slate-800 dark:text-slate-200">
                            <option value="">-- Pilih Alat Scanner RFID --</option>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}">{{ $device->name }} (API Key: {{ substr($device->api_key, 0, 8) }}...)</option>
                            @endforeach
                        </select>
                        @if(count($devices) === 0)
                            <p class="text-xs text-rose-500 font-semibold mt-2">Peringatan: Belum ada device tipe 'rfid_perpus_pinjam' yang aktif.</p>
                        @endif
                    </div>
                </div>

                <!-- MANUAL MODE OPTIONS (REALTIME SEARCH DROP DOWN) -->
                <div x-show="inputMode === 'manual'" class="space-y-4" x-cloak
                     x-data="{ openDropdown: false }">
                    <p class="text-xs text-slate-400">Cari berdasarkan NIS/NIP, Kelas/Divisi, atau Nama:</p>
                    
                    <div class="relative">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pilih Anggota Perpustakaan</label>
                        
                        <!-- Trigger Selector text display -->
                        <div @click="openDropdown = !openDropdown" 
                             class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus-within:border-indigo-600 font-semibold text-slate-800 dark:text-slate-200 flex justify-between items-center cursor-pointer select-none">
                            <span x-text="selectedMemberId ? selectedMemberText : '-- Pilih Anggota (Cari NIS/NIP/Nama) --'"></span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="openDropdown ? 'rotate-180' : ''"></i>
                        </div>
                        
                        <!-- Search Dropdown list panel -->
                        <div x-show="openDropdown" 
                             @click.away="openDropdown = false"
                             x-transition
                             class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden max-h-60 flex flex-col"
                             x-cloak>
                             
                            <!-- Dropdown filter input query -->
                            <div class="p-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                                <input type="text" 
                                       x-model="memberSearchQuery" 
                                       @keydown.escape="openDropdown = false"
                                       placeholder="Ketik NIS, NIP, nama kelas atau nama..." 
                                       class="w-full bg-transparent text-xs text-slate-800 dark:text-slate-200 focus:outline-none">
                                <button type="button" x-show="memberSearchQuery" @click="memberSearchQuery = ''" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xs"></i></button>
                            </div>
                            
                            <!-- Search Options Scroll list -->
                            <div class="overflow-y-auto max-h-48 divide-y divide-slate-100 dark:divide-slate-800/40 text-xs">
                                <template x-for="member in filteredMembers" :key="member.id">
                                    <div @click="selectedMemberId = member.id; selectedMemberText = member.name + ' (' + (member.type === 'siswa' ? 'Kelas: ' + member.class_or_dept + ' - NIS/NISN: ' : 'NIP: ') + member.code + ')'; openDropdown = false; memberSearchQuery = ''"
                                         class="p-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/20 cursor-pointer transition-colors text-left flex flex-col gap-0.5">
                                        <div class="font-bold text-slate-900 dark:text-slate-100" x-text="member.name"></div>
                                        <div class="text-slate-500 dark:text-slate-400 font-medium text-[11px] mt-0.5">
                                            <span x-text="member.type === 'siswa' ? 'Kelas: ' : 'Divisi: '"></span>
                                            <span class="text-slate-800 dark:text-slate-200 font-semibold" x-text="member.class_or_dept"></span> &bull; 
                                            <span x-text="member.type === 'siswa' ? 'NIS/NISN: ' : 'NIP: '"></span>
                                            <span class="text-slate-800 dark:text-slate-200 font-semibold" x-text="member.code"></span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredMembers.length === 0" class="p-4 text-center text-slate-400 font-medium">
                                    Tidak ada anggota cocok dengan pencarian.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Range Peminjaman (Dari & Sampai Tanggal) -->
                <div class="grid grid-cols-2 gap-4 border-t border-dashed border-slate-200 dark:border-slate-800 pt-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal Pinjam</label>
                        <input type="date" x-model="borrowDate" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors font-semibold text-slate-800 dark:text-slate-200">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Batas Kembali</label>
                        <input type="date" x-model="dueDate" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors font-semibold text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="closeLoanModal()" class="px-5 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl font-bold text-sm">Batal</button>
                    <!-- RFID Submit Button -->
                    <button type="button" @click="startVerification()" :disabled="!deviceId" x-show="inputMode === 'rfid'"
                            class="px-5 py-2.5 bg-indigo-600 disabled:opacity-50 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/20 active:scale-95 transition-all">
                        Mulai Verifikasi Tap
                    </button>
                    <!-- Manual Submit Button -->
                    <button type="button" @click="submitManual()" :disabled="!selectedMemberId" x-show="inputMode === 'manual'" x-cloak
                            class="px-5 py-2.5 bg-indigo-600 disabled:opacity-50 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/20 active:scale-95 transition-all">
                        Lanjut ke Konfirmasi
                    </button>
                </div>
            </div>

            <!-- Modal Content: STEP 1.5 (CONFIRMATION) -->
            <div class="p-6 space-y-6" x-show="step === 'confirm'" x-cloak>
                <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100/50 dark:border-emerald-900/30 rounded-2xl flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-circle-info text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-slate-200">Konfirmasi Peminjaman Buku</h4>
                        <p class="text-xs text-slate-400">Tinjau informasi peminjaman sebelum konfirmasi resmi.</p>
                    </div>
                </div>

                <!-- Confirmation Card Details -->
                <div class="p-5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3.5">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Buku yang Dipinjam</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100" x-text="loanBook.title"></span>
                    </div>

                    <div class="border-t border-slate-200 dark:border-slate-800 pt-2.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Peminjam (Anggota)</span>
                        <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 block" x-text="scannedMember.name"></span>
                        <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5" x-text="scannedMember.class_or_dept + ' • ' + scannedMember.code"></p>
                    </div>

                    <!-- Date Range Display & Edit inputs -->
                    <div class="border-t border-slate-200 dark:border-slate-800 pt-2.5 grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Pinjam</label>
                            <input type="date" x-model="borrowDate" required
                                   class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 focus:outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Batas Kembali</label>
                            <input type="date" x-model="dueDate" required
                                   class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer buttons -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="resetForm()" 
                            class="px-5 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl font-bold text-xs text-slate-500 hover:bg-slate-50">
                        Batal / Reset
                    </button>
                    <button type="button" @click="submitConfirm()" :disabled="submitting"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-indigo-600/20 active:scale-95 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-circle-check" :class="submitting ? 'animate-spin' : ''"></i>
                        <span x-text="submitting ? 'Menyimpan...' : 'Konfirmasi Pinjam'"></span>
                    </button>
                </div>
            </div>

            <!-- Modal Content: STEP 2 (WAITING SCAN / LOADING) -->
            <div class="p-8 text-center space-y-6" x-show="step === 'waiting'">
                <div class="relative w-24 h-24 mx-auto flex items-center justify-center bg-indigo-50 dark:bg-indigo-950/40 rounded-full text-indigo-600 dark:text-indigo-400 text-3xl animate-pulse">
                    <div class="absolute inset-0 rounded-full border border-indigo-600/30 animate-ping"></div>
                    <i class="fa-solid fa-id-card-clip" x-show="inputMode === 'rfid'"></i>
                    <i class="fa-solid fa-arrows-rotate animate-spin-slow" x-show="inputMode === 'manual'"></i>
                </div>

                <div>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100">
                        <span x-show="inputMode === 'rfid'">MENUNGGU TEMPEL KARTU</span>
                        <span x-show="inputMode === 'manual'">MEMPROSES PEMINJAMAN MANUAL</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        <span x-show="inputMode === 'rfid'">Minta siswa/guru untuk menempelkan kartu anggota RFID mereka pada mesin scanner.</span>
                        <span x-show="inputMode === 'manual'">Sedang memverifikasi data dan membuat transaksi peminjaman buku...</span>
                    </p>
                </div>

                <!-- RFID Timer only -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 rounded-xl text-amber-700 dark:text-amber-400 font-bold text-xs" x-show="inputMode === 'rfid'">
                    <i class="fa-solid fa-stopwatch animate-spin-slow"></i>
                    <span>Sesi Berakhir dalam <span x-text="expiresIn"></span> Detik</span>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl text-left border border-slate-100 dark:border-slate-900 max-w-md mx-auto">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Buku yang akan Dipinjam:</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="loanBook.title"></span>
                </div>

                <div class="pt-4 flex justify-center" x-show="inputMode === 'rfid'">
                    <button type="button" @click="resetForm()" class="px-5 py-2.5 border border-slate-200 dark:border-slate-800 text-rose-500 font-bold rounded-xl text-sm hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all">
                        Batalkan Transaksi
                    </button>
                </div>
            </div>

            <!-- Modal Content: STEP 3 (SUCCESS) -->
            <div class="p-8 text-center space-y-6" x-show="step === 'success'">
                <div class="w-20 h-20 mx-auto flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/40 rounded-full text-emerald-600 dark:text-emerald-400 text-4xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div>
                    <h3 class="font-bold text-xl text-slate-900 dark:text-slate-100">PEMINJAMAN BERHASIL!</h3>
                    <p class="text-xs text-slate-400 mt-1">Transaksi peminjaman telah resmi dicatat ke database.</p>
                </div>

                <div class="p-5 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-xl text-left space-y-2 max-w-md mx-auto text-xs">
                    <div class="flex justify-between border-b border-slate-100 dark:border-slate-800 pb-1.5">
                        <span class="text-slate-400 font-semibold">Peminjam</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="memberName"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-semibold">Buku Dipinjam</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="loanBook.title"></span>
                    </div>
                </div>

                <div class="pt-4 flex justify-center">
                    <button type="button" @click="closeLoanModal()" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/20">
                        Tutup & Refresh Halaman
                    </button>
                </div>
            </div>

            <!-- Modal Content: STEP 4 (FAILED) -->
            <div class="p-8 text-center space-y-6" x-show="step === 'failed'">
                <div class="w-20 h-20 mx-auto flex items-center justify-center bg-rose-50 dark:bg-rose-950/40 rounded-full text-rose-600 dark:text-rose-400 text-4xl">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </div>

                <div>
                    <h3 class="font-bold text-xl text-slate-900 dark:text-slate-100">PEMINJAMAN GAGAL!</h3>
                    <p class="text-sm text-rose-500 dark:text-rose-400 mt-1 font-bold" x-text="errorMessage"></p>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-xl text-left text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-md mx-auto">
                    <i class="fa-solid fa-circle-info text-indigo-500 mr-1.5"></i>
                    Semua transaksi peminjaman diwajibkan telah terdaftar di **Buku Tamu Kunjungan** pada hari yang sama. Jika siswa belum berkunjung, catat kunjungan mereka terlebih dahulu.
                </div>

                <div class="pt-4 flex gap-3 justify-center">
                    <button type="button" @click="resetForm()" class="px-5 py-2 border border-slate-200 dark:border-slate-800 font-bold rounded-xl text-sm">
                        Kembali / Batal
                    </button>
                    <!-- RFID retry -->
                    <button type="button" @click="startVerification()" x-show="inputMode === 'rfid'"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/20">
                        Coba Ulang Tap
                    </button>
                    <!-- Manual retry -->
                    <button type="button" @click="submitManual()" x-show="inputMode === 'manual'"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/20">
                        Coba Ulang Manual
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: DELETE CONFIRMATION -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4"
         x-show="openDeleteModal" x-transition x-cloak>
        <div class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
             @click.away="openDeleteModal = false">
            <!-- Icon -->
            <div class="p-8 flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-2xl bg-rose-100 dark:bg-rose-950/40 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-trash-can text-rose-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-2">Hapus Buku?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Anda akan menghapus buku<br>
                    <strong class="text-slate-700 dark:text-slate-200" x-text="'\"' + deleteBookTitle + '\"'"></strong>
                </p>
                <p class="text-xs text-rose-500 mt-2 font-medium">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <!-- Actions -->
            <div class="px-6 pb-6 flex gap-3">
                <button type="button" @click="openDeleteModal = false"
                        class="flex-1 px-4 py-2.5 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Batal
                </button>
                <form :action="'/books/' + deleteBookId" method="POST" class="flex-1" id="deleteBookForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white rounded-xl text-sm font-bold shadow-lg shadow-rose-600/20 transition-all">
                        <i class="fa-solid fa-trash-can mr-1.5"></i> Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bukuCatalog', () => ({
        openAddModal: false,
        openEditModal: false,
        editBook: { id: '', code: '', title: '', author: '', publisher: '', year: '', stock: '', location: '', cover_url: '' },

        // Delete confirmation
        openDeleteModal: false,
        deleteBookId: null,
        deleteBookTitle: '',

        triggerEdit(book) {
            this.editBook = { ...book };
            this.openEditModal = true;
        },

        triggerDelete(id, title) {
            this.deleteBookId = id;
            this.deleteBookTitle = title;
            this.openDeleteModal = true;
        },

        // Peminjaman states
        openLoanModal: false,
        loanBook: { id: '', title: '', code: '', cover_url: '' },
        deviceId: '',
        step: 'input', // 'input', 'waiting', 'confirm', 'success', 'failed'
        pendingId: null,
        expiresIn: 120,
        countdownInterval: null,
        pollingInterval: null,
        memberName: '',
        totalBooks: 0,
        errorMessage: '',
        inputMode: 'rfid', // 'rfid', 'manual'
        selectedMemberId: '',
        selectedMemberText: '',
        members: {!! json_encode($members->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'code' => $m->member_code,
            'type' => $m->source_type,
            'class_or_dept' => $m->class_or_dept ?? 'Guru / Staf'
        ])) !!},
        memberSearchQuery: '',
        
        // Custom dates and confirmation state
        borrowDate: new Date().toISOString().split('T')[0],
        dueDate: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        scannedMember: { id: null, name: '', code: '', class_or_dept: '' },
        submitting: false,

        get filteredMembers() {
            if (!this.memberSearchQuery) {
                return this.members;
            }
            const q = this.memberSearchQuery.toLowerCase();
            return this.members.filter(m => 
                (m.name && m.name.toLowerCase().includes(q)) || 
                (m.code && m.code.toLowerCase().includes(q)) ||
                (m.class_or_dept && m.class_or_dept.toLowerCase().includes(q))
            );
        },

        triggerLoan(book) {
            this.loanBook = { ...book };
            this.openLoanModal = true;
            this.resetForm();
        },

        startVerification() {
            if (!this.deviceId) {
                alert('Silakan pilih perangkat scanner RFID.');
                return;
            }

            this.step = 'waiting';
            this.expiresIn = 120;
            this.errorMessage = '';

            fetch('{{ route('perpus.loan.start-verification') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    book_ids: [this.loanBook.id],
                    device_id: this.deviceId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.pendingId = data.pending_id;
                    this.expiresIn = data.expires_in;
                    
                    this.countdownInterval = setInterval(() => {
                        if (this.expiresIn > 0) {
                            this.expiresIn--;
                        } else {
                            this.stopTimers();
                            this.step = 'failed';
                            this.errorMessage = 'Waktu tempel kartu habis (Timeout).';
                        }
                    }, 1000);

                    this.pollingInterval = setInterval(() => {
                        this.checkScanStatus();
                    }, 2000);
                } else {
                    this.step = 'input';
                    alert(data.message || 'Terjadi kesalahan sistem.');
                }
            })
            .catch(err => {
                this.step = 'input';
                alert('Koneksi jaringan error.');
            });
        },

        submitManual() {
            if (!this.selectedMemberId) {
                alert('Silakan pilih anggota perpustakaan.');
                return;
            }
            const mb = this.members.find(m => m.id == this.selectedMemberId);
            if (mb) {
                this.scannedMember = {
                    id: mb.id,
                    name: mb.name,
                    code: mb.code,
                    class_or_dept: mb.class_or_dept
                };
                this.step = 'confirm';
            }
        },

        checkScanStatus() {
            if (!this.pendingId) return;

            fetch(`/loans/check-scan-status/${this.pendingId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'scanned') {
                    this.stopTimers();
                    this.scannedMember = {
                        id: data.member_id,
                        name: data.member_name,
                        code: data.member_code,
                        class_or_dept: data.class_or_dept
                    };
                    this.step = 'confirm';
                } else if (data.status === 'failed') {
                    this.stopTimers();
                    this.errorMessage = data.message;
                    this.step = 'failed';
                } else if (data.status === 'expired') {
                    this.stopTimers();
                    this.step = 'failed';
                    this.errorMessage = data.message || 'Sesi verifikasi kedaluwarsa.';
                }
            })
            .catch(err => {
                console.error('Polling error:', err);
            });
        },

        submitConfirm() {
            this.submitting = true;
            this.errorMessage = '';

            if (this.inputMode === 'rfid') {
                fetch('{{ route('perpus.loan.confirm-verification') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        pending_id: this.pendingId,
                        borrow_date: this.borrowDate,
                        due_date: this.dueDate
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.submitting = false;
                    if (data.success) {
                        this.memberName = data.member_name;
                        this.totalBooks = data.total_books;
                        this.step = 'success';
                    } else {
                        this.step = 'failed';
                        this.errorMessage = data.message || 'Konfirmasi peminjaman gagal.';
                    }
                })
                .catch(err => {
                    this.submitting = false;
                    this.step = 'failed';
                    this.errorMessage = 'Terjadi kesalahan koneksi jaringan.';
                });
            } else {
                fetch('{{ route('perpus.loan.store-manual') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        book_ids: [this.loanBook.id],
                        member_id: this.selectedMemberId,
                        borrow_date: this.borrowDate,
                        due_date: this.dueDate
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.submitting = false;
                    if (data.success) {
                        this.memberName = data.member_name;
                        this.totalBooks = data.total_books;
                        this.step = 'success';
                    } else {
                        this.step = 'failed';
                        this.errorMessage = data.message || 'Peminjaman manual gagal.';
                    }
                })
                .catch(err => {
                    this.submitting = false;
                    this.step = 'failed';
                    this.errorMessage = 'Terjadi kesalahan koneksi jaringan.';
                });
            }
        },

        stopTimers() {
            if (this.countdownInterval) clearInterval(this.countdownInterval);
            if (this.pollingInterval) clearInterval(this.pollingInterval);
        },

        resetForm() {
            this.stopTimers();
            this.step = 'input';
            this.pendingId = null;
            this.errorMessage = '';
            this.inputMode = 'rfid';
            this.selectedMemberId = '';
            this.selectedMemberText = '';
            this.memberSearchQuery = '';
            this.submitting = false;
            this.borrowDate = new Date().toISOString().split('T')[0];
            this.dueDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            this.scannedMember = { id: null, name: '', code: '', class_or_dept: '' };
        },

        closeLoanModal() {
            this.stopTimers();
            this.openLoanModal = false;
            if (this.step === 'success') {
                window.location.reload();
            }
        }
    }));
});
</script>
@endpush
@endsection
