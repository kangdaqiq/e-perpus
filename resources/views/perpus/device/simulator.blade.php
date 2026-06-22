@extends('perpus.layouts.app')

@section('title', 'Simulator RFID')

@section('content')
<div x-data="rfidSimulator()">
    <!-- Header Page Actions -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-1.5">
                <a href="{{ route('perpus.device.index') }}" class="hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i> Scanner RFID
                </a>
                <span>/</span>
                <span class="text-slate-400">Simulator Pengujian API</span>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Simulator Scan RFID</h2>
            <p class="text-sm text-slate-400">Simulasikan pembacaan kartu RFID (tap kartu) tanpa menggunakan perangkat keras scanner fisik.</p>
        </div>
        <div>
            <a href="{{ route('perpus.device.index') }}" class="px-5 py-3 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 font-semibold rounded-2xl transition-all duration-150 flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <i class="fa-solid fa-server"></i>
                <span>Kelola Alat Scanner</span>
            </a>
        </div>
    </div>

    <!-- Main Simulator Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT: SIMULATOR FORM -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
            <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200 flex items-center gap-2">
                <i class="fa-solid fa-gamepad text-emerald-600"></i>
                <span>Simulasi Tap Kartu</span>
            </h3>

            <!-- Form -->
            <div class="space-y-4">
                <!-- Preset Scanner -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pilih Scanner (Alat)</label>
                    <select id="presetDeviceSelect" @change="selectPresetDevice($el.value)" 
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors">
                        <option value="">-- Pilih Scanner Aktif --</option>
                        @foreach($devices as $dev)
                            <option value="{{ $dev->id }}" data-key="{{ $dev->api_key }}" data-type="{{ $dev->type }}">{{ $dev->name }} ({{ $dev->type === 'rfid_perpus_kunjungan' ? 'Buku Tamu' : 'Peminjaman' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Preset Member -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pilih Anggota (Preset)</label>
                    <select id="presetMemberSelect" @change="selectPresetMember($el.value)" 
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm focus:outline-none focus:border-indigo-600 transition-colors">
                        <option value="">-- Pilih Anggota Terdaftar --</option>
                        @foreach($members as $mb)
                            <option value="{{ $mb->rfid_uid }}">{{ $mb->name }} ({{ strtoupper($mb->type) }} - {{ $mb->rfid_uid }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="border-t border-dashed border-slate-200 dark:border-slate-800 my-4"></div>

                <!-- API Key Input -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">API Key Perangkat</label>
                    <input type="text" x-model="apiKey" required 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-mono focus:outline-none focus:border-indigo-600 transition-colors" 
                           placeholder="Masukkan API Key Scanner">
                </div>

                <!-- UID Input -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">UID Kartu RFID</label>
                    <input type="text" x-model="uid" required 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl text-sm font-mono focus:outline-none focus:border-indigo-600 transition-colors" 
                           placeholder="Masukkan UID Kartu (Contoh: 12A34B5C)">
                </div>

                <div class="pt-4">
                    <button type="button" @click="sendRfidTap()" 
                            :disabled="loading"
                            class="w-full px-5 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 disabled:opacity-50 text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/20 transition-all duration-150 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-tower-broadcast" :class="loading ? 'animate-pulse' : ''"></i>
                        <span x-text="loading ? 'Mengirim Data...' : 'Kirim Simulasi Tap Kartu (POST)'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- RIGHT: API RESPONSE -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            <!-- Payload Info -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm flex-1 space-y-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-200 flex items-center gap-2">
                    <i class="fa-solid fa-code text-indigo-600"></i>
                    <span>Hasil Respons API</span>
                </h3>

                <!-- Placeholder when no request sent -->
                <div x-show="!hasResult" class="py-12 text-center text-slate-400 space-y-3">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-300 animate-bounce"></i>
                    <p class="text-xs max-w-xs mx-auto">Belum ada request yang dikirim. Isi form simulator di sebelah kiri lalu klik tombol Kirim.</p>
                </div>

                <!-- Request Status & Alert -->
                <div x-show="hasResult" class="space-y-4" x-cloak>
                    <!-- Status Badge -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase">HTTP Status:</span>
                        <span :class="statusSuccess ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400'"
                              class="px-2.5 py-1 text-xs rounded-full font-bold font-mono" x-text="httpStatus">
                        </span>
                    </div>

                    <!-- Alert message -->
                    <div :class="apiOk ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 border-rose-200 dark:bg-rose-950/20 dark:border-rose-800 text-rose-700 dark:text-rose-400'"
                         class="p-4 border rounded-2xl flex items-start gap-3">
                        <i :class="apiOk ? 'fa-solid fa-circle-check text-emerald-500' : 'fa-solid fa-circle-xmark text-rose-500'" 
                           class="text-xl flex-shrink-0 mt-0.5"></i>
                        <div>
                            <h4 class="font-bold text-sm" x-text="apiMessage"></h4>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 font-semibold" x-show="scannedName">
                                Peminjam/Pengunjung: <span class="text-slate-800 dark:text-slate-200 font-bold" x-text="scannedName"></span>
                            </p>
                        </div>
                    </div>

                    <!-- JSON Body codeblock -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">JSON Response</label>
                        <pre class="w-full p-4 bg-slate-950 text-emerald-400 font-mono text-xs rounded-2xl overflow-x-auto select-all max-h-48" x-text="jsonResponse"></pre>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- PRESETS TABLES (CHEAT SHEET) -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Scanner Cheat Sheet -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <h4 class="font-bold text-sm text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-server text-indigo-500"></i>
                <span>Daftar Scanner RFID Aktif</span>
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800 pb-2">
                            <th class="pb-2">Nama Perangkat</th>
                            <th class="pb-2">Tipe</th>
                            <th class="pb-2 text-right">Opsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($devices as $dev)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                <td class="py-2.5 font-bold text-slate-800 dark:text-slate-200">{{ $dev->name }}</td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $dev->type === 'rfid_perpus_kunjungan' ? 'bg-amber-50 text-amber-600 dark:bg-amber-950/20' : 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20' }}">
                                        {{ $dev->type === 'rfid_perpus_kunjungan' ? 'Buku Tamu' : 'Peminjaman' }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right">
                                    <button @click="selectPresetDeviceFromTable('{{ $dev->id }}', '{{ $dev->api_key }}')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 font-bold rounded-lg transition-all active:scale-95">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-slate-400">Tidak ada scanner aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Member Cheat Sheet -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
            <h4 class="font-bold text-sm text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-users text-emerald-500"></i>
                <span>Daftar RFID Anggota Terdaftar</span>
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800 pb-2">
                            <th class="pb-2">Nama Anggota</th>
                            <th class="pb-2">Tipe</th>
                            <th class="pb-2 font-mono">RFID UID</th>
                            <th class="pb-2 text-right">Opsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($members as $mb)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20">
                                <td class="py-2.5 font-bold text-slate-800 dark:text-slate-200">{{ $mb->name }}</td>
                                <td class="py-2.5 uppercase text-[10px] font-semibold text-slate-400">{{ $mb->type }}</td>
                                <td class="py-2.5 font-mono text-slate-600 dark:text-slate-400">{{ $mb->rfid_uid }}</td>
                                <td class="py-2.5 text-right">
                                    <button @click="selectPresetMemberFromTable('{{ $mb->rfid_uid }}')" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold rounded-lg transition-all active:scale-95">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400">Tidak ada data anggota dengan RFID.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rfidSimulator', () => ({
        apiKey: '',
        uid: '',
        loading: false,
        hasResult: false,
        httpStatus: '',
        statusSuccess: true,
        apiOk: false,
        apiMessage: '',
        scannedName: '',
        jsonResponse: '',

        selectPresetDevice(deviceId) {
            if (!deviceId) {
                this.apiKey = '';
                return;
            }
            const selectEl = document.getElementById('presetDeviceSelect');
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (selectedOpt) {
                this.apiKey = selectedOpt.getAttribute('data-key') || '';
            }
        },

        selectPresetDeviceFromTable(deviceId, apiKey) {
            this.apiKey = apiKey;
            const selectEl = document.getElementById('presetDeviceSelect');
            selectEl.value = deviceId;
        },

        selectPresetMember(uidValue) {
            this.uid = uidValue;
        },

        selectPresetMemberFromTable(uidValue) {
            this.uid = uidValue;
            const selectEl = document.getElementById('presetMemberSelect');
            selectEl.value = uidValue;
        },

        sendRfidTap() {
            if (!this.apiKey || !this.uid) {
                alert('API Key dan UID RFID harus diisi!');
                return;
            }

            this.loading = true;
            this.hasResult = false;
            this.scannedName = '';

            fetch('/api/rfid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    api_key: this.apiKey,
                    uid: this.uid
                })
            })
            .then(res => {
                this.httpStatus = `${res.status} ${res.statusText}`;
                this.statusSuccess = res.ok;
                return res.json().then(data => ({ status: res.status, ok: res.ok, data }));
            })
            .then(({ status, ok, data }) => {
                this.loading = false;
                this.hasResult = true;
                
                this.apiOk = data.ok;
                this.apiMessage = data.message || 'Respons tanpa pesan';
                this.scannedName = data.nama || '';
                this.jsonResponse = JSON.stringify(data, null, 4);
            })
            .catch(err => {
                this.loading = false;
                this.hasResult = true;
                this.httpStatus = 'Error Connection';
                this.statusSuccess = false;
                this.apiOk = false;
                this.apiMessage = 'Koneksi jaringan error/gagal menghubungi endpoint API.';
                this.jsonResponse = JSON.stringify({ error: err.message }, null, 4);
            });
        }
    }));
});
</script>
@endpush
@endsection
