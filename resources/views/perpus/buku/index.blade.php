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
            <button @click="resetAddBookForm(); openAddModal = true" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-semibold rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-150 flex items-center gap-2">
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
                                        <img src="{{ asset($book->cover_url) }}" alt="Cover" class="w-full h-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                                        <i class="fa-solid fa-book-bookmark text-slate-400 text-lg" style="display:none;"></i>
                                    @else
                                        <i class="fa-solid fa-book-bookmark text-slate-300 text-lg"></i>
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
        <div class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <i class="fa-solid fa-book text-indigo-600"></i>
                    <span>Tambah Buku Baru</span>
                </h3>
                <button @click="openAddModal = false" class="text-slate-400 hover:text-slate-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('perpus.buku.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                @csrf
                <input type="hidden" name="_form" value="add">

                @if($errors->any() && old('_form') === 'add')
                    <div class="p-4 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 rounded-2xl text-xs text-rose-600 dark:text-rose-400 space-y-1">
                        <div class="font-bold flex items-center gap-1.5 mb-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span>Gagal menyimpan buku:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- AI Vision OCR Banner & Trigger -->
                <div class="p-4 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 border border-indigo-200 dark:border-indigo-800/50 rounded-2xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md shadow-indigo-600/30 shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                    <span>Isi Otomatis dengan Foto</span>
                                    <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 text-[10px] rounded-full uppercase tracking-wider font-extrabold">Smart Scan</span>
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Foto sampul atau halaman informasi buku untuk auto-fill form.</p>
                            </div>
                        </div>

                        <div>
                            <label for="aiOcrFileInput" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0 select-none">
                                <i class="fa-solid fa-camera"></i>
                                <span>Scan / Upload Foto</span>
                            </label>
                            <input type="file" id="aiOcrFileInput" accept="image/*" capture="environment" class="hidden" @change="handleOcrScan($event)">
                        </div>
                    </div>

                    <!-- OCR Loading State -->
                    <div x-show="isScanningOcr" x-transition class="mt-3 p-3 bg-white/90 dark:bg-slate-900/90 rounded-xl border border-indigo-100 dark:border-indigo-950 flex items-center gap-3 text-xs text-indigo-600 dark:text-indigo-400 font-semibold">
                        <i class="fa-solid fa-circle-notch fa-spin text-base"></i>
                        <span>Sedang menganalisis foto & mengekstrak informasi buku...</span>
                    </div>

                    <!-- OCR Success Message -->
                    <div x-show="ocrSuccessMessage && !isScanningOcr" x-transition class="mt-3 p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-900 text-xs text-emerald-700 dark:text-emerald-300 font-semibold flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                            <span x-text="ocrSuccessMessage"></span>
                        </span>
                        <button type="button" @click="ocrSuccessMessage = ''" class="text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    <!-- OCR Error Message -->
                    <div x-show="ocrErrorMessage && !isScanningOcr" x-transition class="mt-3 p-3 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-900 text-xs text-rose-700 dark:text-rose-300 font-semibold flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm"></i>
                            <span x-text="ocrErrorMessage"></span>
                        </span>
                        <button type="button" @click="ocrErrorMessage = ''" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    <!-- Image Preview Thumbnail -->
                    <template x-if="ocrPreviewUrl">
                        <div class="mt-3 flex items-center gap-3 p-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
                            <img :src="ocrPreviewUrl" alt="Scan Preview" class="w-12 h-16 object-cover rounded-lg border border-slate-200 dark:border-slate-700">
                            <div class="text-xs text-slate-500">
                                <span class="font-bold text-slate-700 dark:text-slate-300">Foto Hasil Scan AI</span>
                                <p class="text-[11px] text-slate-400">Foto ini otomatis diset sebagai Cover Buku.</p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kode Buku / ISBN</label>
                        <input type="text" name="code" required x-model="newBook.code" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Judul Buku</label>
                        <input type="text" name="title" required x-model="newBook.title" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Penulis</label>
                        <input type="text" name="author" x-model="newBook.author" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Penerbit</label>
                        <input type="text" name="publisher" x-model="newBook.publisher" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tahun Terbit</label>
                        <input type="number" name="year" x-model="newBook.year" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Stok Awal</label>
                        <input type="number" name="stock" min="1" required x-model="newBook.stock" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Lokasi Rak</label>
                        <input type="text" name="location" x-model="newBook.location" placeholder="R-01" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Cover Image (Opsional)</label>
                    <input type="file" id="addBookCoverInput" name="cover" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
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
        <div class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg">Edit Buku</h3>
                <button @click="openEditModal = false" class="text-slate-400 hover:text-slate-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="`/books/${editBook.id}`" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
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
        <div class="w-full max-w-xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-visible shadow-indigo-500/10">
            
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

                <!-- Borrower Type Toggle (Siswa vs Guru) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Pilih Tipe Peminjam</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="borrowerType = 'siswa'; selectedTeacherId = ''; selectedTeacherText = ''; teacherInfo = null;" 
                                class="py-2.5 px-4 text-xs font-bold rounded-2xl border transition-all flex items-center justify-center gap-2 cursor-pointer select-none"
                                :class="borrowerType === 'siswa' ? 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-500 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700'">
                            <i class="fa-solid fa-user-graduate"></i> Siswa
                        </button>
                        <button type="button" @click="borrowerType = 'guru'; selectedMemberId = ''; selectedMemberText = '';" 
                                class="py-2.5 px-4 text-xs font-bold rounded-2xl border transition-all flex items-center justify-center gap-2 cursor-pointer select-none"
                                :class="borrowerType === 'guru' ? 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-500 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-700'">
                            <i class="fa-solid fa-chalkboard-user"></i> Guru / Staf
                        </button>
                    </div>
                </div>

                <!-- GURU REALTIME SEARCH DROPDOWN (Shown when borrowerType === 'guru') -->
                <div x-show="borrowerType === 'guru'" class="space-y-2" x-data="{ openTeacherDropdown: false }" x-cloak>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Pilih Guru (Peminjam Utama)</label>
                    <div class="relative">
                        <div @click="openTeacherDropdown = !openTeacherDropdown" 
                             class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-semibold text-slate-800 dark:text-slate-200 flex justify-between items-center cursor-pointer select-none">
                            <span x-text="selectedTeacherId ? selectedTeacherText : '-- Cari & Pilih Guru (NIP / Nama) --'"></span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="openTeacherDropdown ? 'rotate-180' : ''"></i>
                        </div>
                        
                        <div x-show="openTeacherDropdown" 
                             @click.away="openTeacherDropdown = false"
                             x-transition
                             class="absolute z-50 w-full mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden max-h-60 flex flex-col"
                             x-cloak>
                            <div class="p-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 flex items-center gap-2">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                                <input type="text" 
                                       x-model="teacherSearchQuery" 
                                       @keydown.escape="openTeacherDropdown = false"
                                       placeholder="Ketik NIP, nama atau divisi guru..." 
                                       class="w-full bg-transparent text-xs text-slate-800 dark:text-slate-200 focus:outline-none">
                                <button type="button" x-show="teacherSearchQuery" @click="teacherSearchQuery = ''" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xs"></i></button>
                            </div>
                            
                            <div class="overflow-y-auto max-h-48 divide-y divide-slate-100 dark:divide-slate-800/40 text-xs">
                                <template x-for="teacher in filteredTeachers" :key="teacher.id">
                                    <div @click="selectedTeacherId = teacher.id; selectedTeacherText = teacher.name + ' (NIP: ' + teacher.code + ' - ' + teacher.class_or_dept + ')'; openTeacherDropdown = false; teacherSearchQuery = ''"
                                         class="p-3 hover:bg-indigo-50 dark:hover:bg-indigo-950/20 cursor-pointer transition-colors text-left flex flex-col gap-0.5">
                                        <div class="font-bold text-slate-900 dark:text-slate-100" x-text="teacher.name"></div>
                                        <div class="text-slate-500 dark:text-slate-400 font-medium text-[11px] mt-0.5">
                                            Divisi/Jabatan: <span class="text-slate-800 dark:text-slate-200 font-semibold" x-text="teacher.class_or_dept"></span> &bull; 
                                            NIP: <span class="text-slate-800 dark:text-slate-200 font-semibold" x-text="teacher.code"></span>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredTeachers.length === 0" class="p-4 text-center text-slate-400 font-medium">
                                    Tidak ada data guru yang cocok dengan pencarian.
                                </div>
                            </div>
                        </div>
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
                        <i class="fa-solid fa-keyboard mr-1.5"></i> Input Manual
                    </button>
                </div>

                <!-- RFID MODE OPTIONS -->
                <div x-show="inputMode === 'rfid'" class="space-y-4">
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
                            <p class="text-xs text-rose-500 font-semibold mt-2">Peringatan: Belum ada device yang aktif.</p>
                        @endif
                    </div>
                    <!-- Info Banner when Borrower is Guru -->
                    <div x-show="borrowerType === 'guru'" class="p-3 bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl text-xs text-indigo-700 dark:text-indigo-300 flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-info text-indigo-500 text-sm"></i>
                        <span>Verifikasi dilakukan dengan memindai <strong>kartu RFID milik Siswa</strong> yang ditugaskan untuk mengambil buku.</span>
                    </div>
                </div>

                <!-- MANUAL MODE OPTIONS (REALTIME SEARCH DROP DOWN) -->
                <div x-show="inputMode === 'manual'" class="space-y-4" x-cloak
                     x-data="{ openDropdown: false }">
                    <p class="text-xs text-slate-400" x-text="borrowerType === 'guru' ? 'Pilih siswa yang ditugaskan untuk mengambil buku:' : 'Cari berdasarkan NIS/NIP, Kelas/Divisi, atau Nama:'"></p>
                    
                    <div class="relative">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2" x-text="borrowerType === 'guru' ? 'Pilih Siswa (Pengambil Buku)' : 'Pilih Anggota Perpustakaan'"></label>
                        
                        <!-- Trigger Selector text display -->
                        <div @click="openDropdown = !openDropdown" 
                             class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus-within:border-indigo-600 font-semibold text-slate-800 dark:text-slate-200 flex justify-between items-center cursor-pointer select-none">
                            <span x-text="selectedMemberId ? selectedMemberText : (borrowerType === 'guru' ? '-- Pilih Siswa Pengambil Buku --' : '-- Pilih Anggota (Cari NIS/NIP/Nama) --')"></span>
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
                                    <div @click="selectedMemberId = member.id; selectedMemberText = member.name + ' (' + (member.type === 'siswa' ? 'Kelas: ' + member.class_or_dept + ' - NIS: ' : 'NIP: ') + member.code + ')'; openDropdown = false; memberSearchQuery = ''"
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

                <!-- Range Peminjaman (Dari & Sampai Tanggal) & Qty -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-dashed border-slate-200 dark:border-slate-800 pt-4">
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
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jumlah (Qty)</label>
                        <input type="number" x-model="qty" min="1" :max="loanBook.sisa_stok" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors font-semibold text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="closeLoanModal()" class="px-5 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl font-bold text-sm">Batal</button>
                    <!-- RFID Submit Button -->
                    <button type="button" @click="startVerification()" :disabled="!deviceId || (borrowerType === 'guru' && !selectedTeacherId)" x-show="inputMode === 'rfid'"
                            class="px-5 py-2.5 bg-indigo-600 disabled:opacity-50 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/20 active:scale-95 transition-all">
                        Mulai Verifikasi Tap
                    </button>
                    <!-- Manual Submit Button -->
                    <button type="button" @click="submitManual()" :disabled="!selectedMemberId || (borrowerType === 'guru' && !selectedTeacherId)" x-show="inputMode === 'manual'" x-cloak
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
                        <span class="text-sm font-bold text-slate-800 dark:text-slate-100" x-text="loanBook.title + ' (Qty: ' + qty + ')'"></span>
                    </div>

                    <!-- Mode Guru -->
                    <template x-if="borrowerType === 'guru'">
                        <div class="space-y-3 border-t border-slate-200 dark:border-slate-800 pt-2.5">
                            <div>
                                <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider block">Peminjam Utama (Guru)</span>
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 block" x-text="teacherInfo ? teacherInfo.name : selectedTeacherText"></span>
                                <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5" x-text="teacherInfo ? ('NIP: ' + teacherInfo.code + ' • ' + teacherInfo.class_or_dept) : ''"></p>
                            </div>
                            <div class="border-t border-dashed border-slate-200 dark:border-slate-800 pt-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pengambil Buku (Siswa Scan/Pilih)</span>
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-100 block" x-text="scannedMember.name"></span>
                                <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5" x-text="scannedMember.class_or_dept + ' • ' + scannedMember.code"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Mode Siswa -->
                    <template x-if="borrowerType === 'siswa'">
                        <div class="border-t border-slate-200 dark:border-slate-800 pt-2.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Peminjam (Anggota)</span>
                            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400 block" x-text="scannedMember.name"></span>
                            <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5" x-text="scannedMember.class_or_dept + ' • ' + scannedMember.code"></p>
                        </div>
                    </template>

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
                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="loanBook.title + ' (Qty: ' + qty + ')'"></span>
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
        <div class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
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
        openAddModal: {{ $errors->any() && old('_form') === 'add' ? 'true' : 'false' }},
        openEditModal: false,
        newBook: { code: '{{ old('code') }}', title: '{{ old('title') }}', author: '{{ old('author') }}', publisher: '{{ old('publisher') }}', year: '{{ old('year') }}', stock: '{{ old('stock', 1) }}', location: '{{ old('location') }}' },
        editBook: { id: '', code: '', title: '', author: '', publisher: '', year: '', stock: '', location: '', cover_url: '' },
        
        // AI Vision OCR States
        isScanningOcr: false,
        ocrErrorMessage: '',
        ocrSuccessMessage: '',
        ocrPreviewUrl: '',

        compressImage(file, maxWidth = 1200, maxHeight = 1200, quality = 0.82) {
            return new Promise((resolve) => {
                if (!file || file.size < 800 * 1024) {
                    resolve(file);
                    return;
                }

                const img = new Image();
                const reader = new FileReader();

                reader.onload = (e) => {
                    img.src = e.target.result;
                };

                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth || height > maxHeight) {
                        if (width > height) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        } else {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (blob) {
                            const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, "") + ".jpg", {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            resolve(compressedFile);
                        } else {
                            resolve(file);
                        }
                    }, 'image/jpeg', quality);
                };

                img.onerror = () => resolve(file);
                reader.readAsDataURL(file);
            });
        },

        resetAddBookForm() {
            this.newBook = { code: '', title: '', author: '', publisher: '', year: '', stock: 1, location: '' };
            this.isScanningOcr = false;
            this.ocrErrorMessage = '';
            this.ocrSuccessMessage = '';
            this.ocrPreviewUrl = '';
            const coverInput = document.getElementById('addBookCoverInput');
            if (coverInput) coverInput.value = '';
            const ocrInput = document.getElementById('aiOcrFileInput');
            if (ocrInput) ocrInput.value = '';
        },

        async handleOcrScan(event) {
            let file = event.target.files[0];
            if (!file) return;

            this.isScanningOcr = true;
            this.ocrErrorMessage = '';
            this.ocrSuccessMessage = '';

            try {
                file = await this.compressImage(file, 1200, 1200, 0.82);
            } catch (e) {
                console.warn('Compression skipped:', e);
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.ocrPreviewUrl = e.target.result;
            };
            reader.readAsDataURL(file);

            const formData = new FormData();
            formData.append('image', file);

            fetch('{{ route('perpus.buku.scan-ocr') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.isScanningOcr = false;
                if (data.success) {
                    if (data.data.code) this.newBook.code = data.data.code;
                    if (data.data.title) this.newBook.title = data.data.title;
                    if (data.data.author) this.newBook.author = data.data.author;
                    if (data.data.publisher) this.newBook.publisher = data.data.publisher;
                    if (data.data.year) this.newBook.year = data.data.year;

                    this.ocrSuccessMessage = 'Informasi buku berhasil diekstrak oleh AI Gemini!';

                    const coverInput = document.getElementById('addBookCoverInput');
                    if (coverInput) {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        coverInput.files = dt.files;
                    }
                } else {
                    this.ocrErrorMessage = data.message || 'Gagal memindai gambar buku dengan AI.';
                }
            })
            .catch(err => {
                this.isScanningOcr = false;
                this.ocrErrorMessage = 'Terjadi kesalahan koneksi saat memproses scan AI.';
            });
        },

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
        borrowerType: 'siswa', // 'siswa', 'guru'
        selectedTeacherId: '',
        selectedTeacherText: '',
        teacherSearchQuery: '',
        teacherInfo: null,
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
        qty: 1,

        get filteredTeachers() {
            const list = this.members.filter(m => m.type === 'guru');
            if (!this.teacherSearchQuery) {
                return list;
            }
            const q = this.teacherSearchQuery.toLowerCase();
            return list.filter(m => 
                (m.name && m.name.toLowerCase().includes(q)) || 
                (m.code && m.code.toLowerCase().includes(q)) ||
                (m.class_or_dept && m.class_or_dept.toLowerCase().includes(q))
            );
        },

        get filteredMembers() {
            const list = this.borrowerType === 'guru' 
                ? this.members.filter(m => m.type === 'siswa')
                : this.members;
            if (!this.memberSearchQuery) {
                return list;
            }
            const q = this.memberSearchQuery.toLowerCase();
            return list.filter(m => 
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
            const quantity = parseInt(this.qty);
            if (isNaN(quantity) || quantity < 1) {
                alert('Jumlah pinjam minimal adalah 1 buku.');
                return;
            }
            if (quantity > this.loanBook.sisa_stok) {
                alert('Jumlah pinjam tidak boleh melebihi sisa stok yang tersedia (' + this.loanBook.sisa_stok + ').');
                return;
            }
            if (!this.deviceId) {
                alert('Silakan pilih perangkat scanner RFID.');
                return;
            }
            if (this.borrowerType === 'guru' && !this.selectedTeacherId) {
                alert('Silakan pilih Guru (Peminjam Utama) terlebih dahulu.');
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
                    device_id: this.deviceId,
                    qty: this.qty,
                    borrower_type: this.borrowerType,
                    teacher_id: this.borrowerType === 'guru' ? this.selectedTeacherId : null
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
            const quantity = parseInt(this.qty);
            if (isNaN(quantity) || quantity < 1) {
                alert('Jumlah pinjam minimal adalah 1 buku.');
                return;
            }
            if (quantity > this.loanBook.sisa_stok) {
                alert('Jumlah pinjam tidak boleh melebihi sisa stok yang tersedia (' + this.loanBook.sisa_stok + ').');
                return;
            }
            if (this.borrowerType === 'guru') {
                if (!this.selectedTeacherId) {
                    alert('Silakan pilih Guru (Peminjam Utama).');
                    return;
                }
                if (!this.selectedMemberId) {
                    alert('Silakan pilih Siswa yang ditugaskan untuk mengambil buku.');
                    return;
                }
                const teacher = this.members.find(m => m.id == this.selectedTeacherId);
                const student = this.members.find(m => m.id == this.selectedMemberId);
                if (teacher) {
                    this.teacherInfo = {
                        id: teacher.id,
                        name: teacher.name,
                        code: teacher.code,
                        class_or_dept: teacher.class_or_dept
                    };
                }
                if (student) {
                    this.scannedMember = {
                        id: student.id,
                        name: student.name,
                        code: student.code,
                        class_or_dept: student.class_or_dept
                    };
                }
                this.step = 'confirm';
            } else {
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
                    if (data.teacher) {
                        this.teacherInfo = data.teacher;
                    }
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
                        borrower_type: this.borrowerType,
                        teacher_id: this.borrowerType === 'guru' ? this.selectedTeacherId : null,
                        borrow_date: this.borrowDate,
                        due_date: this.dueDate,
                        qty: this.qty
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
            this.borrowerType = 'siswa';
            this.selectedTeacherId = '';
            this.selectedTeacherText = '';
            this.teacherSearchQuery = '';
            this.teacherInfo = null;
            this.selectedMemberId = '';
            this.selectedMemberText = '';
            this.memberSearchQuery = '';
            this.submitting = false;
            this.borrowDate = new Date().toISOString().split('T')[0];
            this.dueDate = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
            this.scannedMember = { id: null, name: '', code: '', class_or_dept: '' };
            this.qty = 1;
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
