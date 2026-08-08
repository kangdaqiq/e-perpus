@extends('perpus.layouts.app')

@section('title', 'Kelola Admin Pembantu')

@section('content')
<div x-data="{ showCreateModal: false, showEditModal: false, editData: { id: null, full_name: '', username: '', email: '' } }">
    <!-- Header Page Actions -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Kelola Admin Pembantu</h2>
            <p class="text-sm text-slate-400">Setiap sekolah dapat menambahkan maksimal 2 akun Admin Pembantu.</p>
        </div>
        <div>
            @if($count < 2)
                <button @click="showCreateModal = true" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-600/30 active:scale-95 transition-all duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Tambah Admin Pembantu</span>
                </button>
            @else
                <button disabled class="px-6 py-3 bg-slate-300 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold rounded-2xl cursor-not-allowed flex items-center gap-2" title="Batas maksimal 2 akun tercapai">
                    <i class="fa-solid fa-lock"></i>
                    <span>Batas 2 Akun Tercapai</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Alert / Counter Banner -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-3xl shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-950/40 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div>
                <span class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Status Akun Pembantu</span>
                <span class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $count }} / 2 Akun Terpakai</span>
            </div>
        </div>

        <div class="md:col-span-2 bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/40 p-6 rounded-3xl flex items-center gap-4">
            <i class="fa-solid fa-circle-info text-2xl text-indigo-600 dark:text-indigo-400 flex-shrink-0"></i>
            <p class="text-xs sm:text-sm text-indigo-950 dark:text-indigo-200">
                <strong>Catatan Hak Akses:</strong> Akun Admin Pembantu dapat mengelola Katalog Buku, Peminjaman, Kunjungan, Scanner RFID, dan Data Anggota. Akun pembantu tidak dapat menambah atau mengedit akun admin lainnya.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl text-sm font-semibold flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-2xl text-sm font-semibold flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 rounded-2xl text-sm">
            <div class="font-bold mb-1">Terjadi kesalahan validasi:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                        <th class="p-6">Nama Lengkap</th>
                        <th class="p-6">Username</th>
                        <th class="p-6">Email</th>
                        <th class="p-6 text-center">Tgl Dibuat</th>
                        <th class="p-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                    @forelse($assistants as $assistant)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-6">
                                <div class="font-bold text-slate-950 dark:text-slate-100 flex items-center gap-2">
                                    <i class="fa-solid fa-user-shield text-indigo-500"></i>
                                    <span>{{ $assistant->full_name }}</span>
                                </div>
                            </td>
                            <td class="p-6 text-slate-600 dark:text-slate-400 font-semibold">{{ $assistant->username }}</td>
                            <td class="p-6 text-slate-600 dark:text-slate-400">{{ $assistant->email }}</td>
                            <td class="p-6 text-center text-slate-500 text-xs font-semibold">
                                {{ $assistant->created_at ? $assistant->created_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="p-6 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="editData = { id: {{ $assistant->id }}, full_name: '{{ addslashes($assistant->full_name) }}', username: '{{ addslashes($assistant->username) }}', email: '{{ addslashes($assistant->email) }}' }; showEditModal = true"
                                            class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/20 rounded-xl transition-all duration-150" 
                                            title="Edit Admin Pembantu">
                                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                                    </button>
                                    <form action="{{ route('perpus.assistant-admins.destroy', $assistant->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun Admin Pembantu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all duration-150" title="Hapus Akun">
                                            <i class="fa-solid fa-trash-can text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400 font-medium">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <i class="fa-solid fa-user-slash text-4xl text-slate-300 dark:text-slate-700 mb-3"></i>
                                    <span>Belum ada akun Admin Pembantu untuk sekolah ini.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Create -->
    <div x-show="showCreateModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-6" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i>
                    <span>Tambah Admin Pembantu</span>
                </h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('perpus.assistant-admins.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="full_name" required value="{{ old('full_name') }}" placeholder="Contoh: Budi Santoso" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Username</label>
                    <input type="text" name="username" required value="{{ old('username') }}" placeholder="Contoh: adminpembantu1" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" required value="{{ old('email') }}" placeholder="Contoh: pembantu1@sekolah.sch.id" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-600/30">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-6" @click.away="showEditModal = false">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-indigo-600"></i>
                    <span>Edit Admin Pembantu</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="'{{ route('perpus.assistant-admins.index') }}/' + editData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" name="full_name" x-model="editData.full_name" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Username</label>
                    <input type="text" name="username" x-model="editData.username" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" x-model="editData.email" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Biarkan kosong jika tidak diubah" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold focus:outline-none focus:border-indigo-500">
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" @click="showEditModal = false" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-100 dark:hover:bg-slate-800 text-sm">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-600/30">Perbarui Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
