<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Member;
use App\Models\Visit;
use App\Models\PendingVerification;

class RfidController extends Controller
{
    /**
     * Menangani POST request dari scanner RFID (/api/rfid)
     */
    public function handle(Request $request)
    {
        $apiKey = trim($request->input('api_key', ''));
        $uid = strtoupper(trim($request->input('uid', '')));

        // 1. Validasi API Key & Device
        if (empty($apiKey)) {
            return response()->json([
                'ok' => false,
                'message' => 'API Key Kosong',
                'type' => 'error'
            ], 400);
        }

        $device = Device::with('school')->where('api_key', $apiKey)->where('active', true)->first();
        if (!$device || !$device->school || !$device->school->is_perpus_active) {
            return response()->json([
                'ok' => false,
                'message' => 'Device Tidak Terdaftar/Aktif',
                'type' => 'error'
            ], 403);
        }

        // 2. Validasi UID
        if (empty($uid)) {
            return response()->json([
                'ok' => false,
                'message' => 'UID Kosong',
                'type' => 'error'
            ], 400);
        }

        // 3. Cabang Logika Berdasarkan Tipe Device
        if ($device->type === 'rfid_perpus_kunjungan') {
            return $this->handleKunjungan($device, $uid);
        } elseif ($device->type === 'rfid_perpus_pinjam') {
            return $this->handlePeminjaman($device, $uid);
        }

        return response()->json([
            'ok' => false,
            'message' => 'Tipe Device Tidak Dikenal',
            'type' => 'error'
        ], 400);
    }

    /**
     * Logika untuk Tap Kunjungan Perpustakaan (Buku Tamu)
     */
    private function handleKunjungan(Device $device, $uid)
    {
        $member = Member::where('rfid_uid', $uid)
            ->where('school_id', $device->school_id)
            ->first();

        if (!$member) {
            return response()->json([
                'ok' => false,
                'message' => 'Kartu Tdk Dikenal',
                'type' => 'error'
            ]);
        }

        // Simpan log kunjungan
        Visit::create([
            'school_id' => $device->school_id,
            'member_id' => $member->id,
            'purpose' => 'Membaca / Meminjam Buku',
            'scanned_at' => now()
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Kunjungan Berhasil',
            'nama' => $member->name,
            'type' => 'perpus_kunjungan'
        ]);
    }

    /**
     * Logika untuk Tap Verifikasi Peminjaman Buku (Dua Arah & Syarat Kunjungan)
     */
    private function handlePeminjaman(Device $device, $uid)
    {
        // Cari antrean verifikasi yang aktif untuk device ini
        $pending = PendingVerification::where('device_id', $device->id)
            ->where('school_id', $device->school_id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$pending) {
            return response()->json([
                'ok' => false,
                'message' => 'Tdk Ada Antrean',
                'type' => 'error'
            ]);
        }

        // Cari data anggota
        $member = Member::where('rfid_uid', $uid)
            ->where('school_id', $device->school_id)
            ->first();

        if (!$member) {
            $pending->update([
                'status' => 'failed',
                'error_message' => 'Kartu tidak terdaftar'
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Kartu Tdk Terdaftar',
                'type' => 'error'
            ]);
        }

        $txData = $pending->transaction_data;
        $isReturnLookup = isset($txData['type']) && $txData['type'] === 'return_lookup';

        if (!$isReturnLookup) {
            // Apakah anggota sudah tap kunjungan hari ini? Jika belum, otomatis buat kunjungan baru.
            $hasVisitToday = Visit::where('member_id', $member->id)
                ->where('school_id', $device->school_id)
                ->whereDate('scanned_at', today())
                ->exists();

            if (!$hasVisitToday) {
                Visit::create([
                    'school_id' => $device->school_id,
                    'member_id' => $member->id,
                    'purpose' => 'Membaca / Meminjam Buku (Auto)',
                    'scanned_at' => now()
                ]);
            }
        }

        // Sukses verifikasi, update data pending
        $pending->update([
            'status' => 'verified',
            'scanned_uid' => $uid
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Verifikasi Berhasil',
            'nama' => $member->name,
            'type' => $isReturnLookup ? 'perpus_kembali_lookup' : 'perpus_pinjam'
        ]);
    }
}
