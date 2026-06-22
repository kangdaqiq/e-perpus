@extends('perpus.layouts.app')

@section('title', 'Scanner RFID')

@section('content')
<div x-data="{ 
    openAddModal: false, 
    openEditModal: false,
    editDevice: { id: '', name: '', type: '', active: 1 },
    triggerEdit(device) {
        this.editDevice = { ...device };
        this.openEditModal = true;
    },
    copyApiKey(key) {
        navigator.clipboard.writeText(key).then(() => {
            alert('API Key berhasil disalin ke clipboard!');
        }).catch(err => {
            alert('Gagal menyalin API Key.');
        });
    }
}">
    <!-- Header Page Actions -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Scanner RFID Perpustakaan</h2>
            <p class="text-sm text-slate-400">Daftarkan dan konfigurasikan alat pembaca kartu RFID perpustakaan Anda.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('perpus.device.simulator') }}" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-semibold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all duration-150 flex items-center gap-2">
                <i class="fa-solid fa-tower-broadcast"></i>
                <span>Buka Simulator RFID</span>
            </a>
            <button @click="openAddModal = true" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-semibold rounded-2xl shadow-lg shadow-indigo-600/20 transition-all duration-150 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Daftarkan Alat Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert info konfigurasi -->
    <div class="mb-6 p-5 bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900 rounded-3xl text-sm flex items-start gap-4 leading-relaxed">
        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-lg flex-shrink-0">
            <i class="fa-solid fa-circle-info"></i>
        </div>
        <div>
            <h4 class="font-bold text-slate-800 dark:text-slate-200">Cara Menghubungkan Alat Scanner RFID:</h4>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Salin **API Key** dari alat yang terdaftar di bawah ini, kemudian masukkan ke halaman konfigurasi IP scanner RFID Anda (`http://<IP-Alat>`) pada input **API Key** dan set **API Server URL** ke `http://<IP-PC-Server-Anda>:8001`.</p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/40 text-slate-400 text-xs uppercase font-bold border-b border-slate-200 dark:border-slate-800">
                        <th class="p-6">Nama Perangkat</th>
                        <th class="p-6">Tipe Scanner</th>
                        <th class="p-6">API Key</th>
                        <th class="p-6">Status Alat</th>
                        <th class="p-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm font-medium text-slate-700 dark:text-slate-300">
                    @forelse($devices as $device)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="p-6">
                                <div class="font-bold text-slate-900 dark:text-slate-100">{{ $device->name }}</div>
                                <div class="text-xs text-slate-400 font-medium">Ditambahkan: {{ $device->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="p-6">
                                @if($device->type === 'rfid_perpus_kunjungan')
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 text-xs rounded-full font-bold uppercase tracking-wider">
                                        <i class="fa-solid fa-users mr-1"></i> Scanner Buku Tamu
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 text-xs rounded-full font-bold uppercase tracking-wider">
                                        <i class="fa-solid fa-cart-shopping mr-1"></i> Scanner Peminjaman
                                    </span>
                                @endif
                            </td>
                            <td class="p-6">
                                <div class="flex items-center gap-2">
                                    <code class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-mono text-slate-600 dark:text-slate-400">{{ substr($device->api_key, 0, 10) }}...{{ substr($device->api_key, -5) }}</code>
                                    <button @click="copyApiKey('{{ $device->api_key }}')" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold" title="Salin API Key">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="p-6">
                                @if($device->active)
                                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 font-bold uppercase">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="p-6 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button @click="triggerEdit({{ $device }})" 
                                            class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/20 flex items-center justify-center transition-colors">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('perpus.device.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 flex items-center justify-center transition-colors">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400 font-medium">Belum ada perangkat scanner RFID yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: ADD DEVICE -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4" 
         x-show="openAddModal" x-transition x-cloak>
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden" 
             @click.away="openAddModal = false">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg">Daftarkan Alat Scanner Baru</h3>
                <button @click="openAddModal = false" class="text-slate-400 hover:text-slate-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('perpus.device.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Perangkat</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm" placeholder="Contoh: Scanner Pintu Depan">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipe Scanner</label>
                    <select name="type" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm">
                        <option value="rfid_perpus_kunjungan">Scanner Buku Tamu (Pintu Masuk)</option>
                        <option value="rfid_perpus_pinjam">Scanner Peminjaman Buku (Meja Admin)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Status Alat</label>
                    <select name="active" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm">
                        <option value="1">Aktif / Izinkan Akses</option>
                        <option value="0">Nonaktifkan Alat</option>
                    </select>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openAddModal = false" class="px-4 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/20">Daftarkan Alat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT DEVICE -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4" 
         x-show="openEditModal" x-transition x-cloak>
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden" 
             @click.away="openEditModal = false">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <h3 class="font-bold text-lg">Perbarui Pengaturan Alat</h3>
                <button @click="openEditModal = false" class="text-slate-400 hover:text-slate-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="`/devices/${editDevice.id}`" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Nama Perangkat</label>
                    <input type="text" name="name" required x-model="editDevice.name" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipe Scanner</label>
                    <select name="type" x-model="editDevice.type" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm">
                        <option value="rfid_perpus_kunjungan">Scanner Buku Tamu (Pintu Masuk)</option>
                        <option value="rfid_perpus_pinjam">Scanner Peminjaman Buku (Meja Admin)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Status Alat</label>
                    <select name="active" x-model="editDevice.active" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:outline-none focus:border-indigo-600 text-sm">
                        <option value="1">Aktif / Izinkan Akses</option>
                        <option value="0">Nonaktifkan Alat</option>
                    </select>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2.5 border border-slate-200 dark:border-slate-800 rounded-xl text-sm font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-600/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
