<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Member;
use App\Models\Visit;
use App\Support\RfidUid;
use Illuminate\Http\Request;

/**
 * Dukungan Scanner RFID USB (Keyboard Emulator / HID).
 *
 * Reader USB terhubung langsung ke komputer pustakawan dan "mengetik" UID kartu
 * ke browser, lalu menekan Enter. Halaman web menangkap input tersebut dan
 * mengirimkannya ke endpoint di bawah ini (dengan sesi login admin, bukan API key).
 */
class UsbRfidController extends Controller
{
    /**
     * Cari anggota berdasarkan UID hasil scan USB.
     * Opsional: sertakan daftar pinjaman aktif (untuk pengembalian cepat).
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:50',
            'with_loans' => 'nullable|boolean',
        ]);

        $schoolId = auth()->user()->school_id;
        $uid = RfidUid::clean($request->input('uid'));

        $member = Member::where('school_id', $schoolId)
            ->whereRfidUid($uid)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'uid' => $uid,
                'message' => "Kartu RFID tidak terdaftar (UID: {$uid}).",
            ], 404);
        }

        $payload = [
            'success' => true,
            'uid' => $uid,
            'member' => $this->memberPayload($member),
        ];

        if ($request->boolean('with_loans')) {
            $payload['loans'] = Loan::with('book')
                ->where('member_id', $member->id)
                ->where('school_id', $schoolId)
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->get()
                ->map(fn ($loan) => [
                    'id' => $loan->id,
                    'book_title' => $loan->book->title ?? '-',
                    'borrow_date' => $loan->borrow_date->format('Y-m-d'),
                    'due_date' => $loan->due_date->format('Y-m-d'),
                    'member_name' => $member->name,
                    'qty' => $loan->qty ?? 1,
                ]);
        }

        return response()->json($payload);
    }

    /**
     * Catat kunjungan (Buku Tamu) dari tap kartu pada scanner USB.
     */
    public function visit(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:50',
        ]);

        $schoolId = auth()->user()->school_id;
        $uid = RfidUid::clean($request->input('uid'));

        $member = Member::where('school_id', $schoolId)
            ->whereRfidUid($uid)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'uid' => $uid,
                'message' => "Kartu RFID tidak terdaftar (UID: {$uid}).",
            ], 404);
        }

        // Cegah tap ganda: abaikan jika anggota sudah tercatat dalam 5 menit terakhir
        $recent = Visit::where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->where('scanned_at', '>=', now()->subMinutes(5))
            ->latest('scanned_at')
            ->first();

        if ($recent) {
            return response()->json([
                'success' => true,
                'duplicate' => true,
                'message' => 'Kunjungan sudah tercatat pukul ' . $recent->scanned_at->format('H:i') . '.',
                'member' => $this->memberPayload($member),
            ]);
        }

        $visit = Visit::create([
            'school_id' => $schoolId,
            'member_id' => $member->id,
            'purpose' => 'Membaca / Meminjam Buku',
            'scanned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'duplicate' => false,
            'message' => 'Kunjungan berhasil dicatat.',
            'member' => $this->memberPayload($member),
            'scanned_at' => $visit->scanned_at->format('H:i:s'),
        ]);
    }

    private function memberPayload(Member $member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'code' => $member->member_code,
            'type' => $member->source_type,
            'class_or_dept' => $member->class_or_dept ?? ($member->source_type === 'guru' ? 'Guru / Staf' : 'Siswa'),
            'rfid_uid' => $member->rfid_uid,
        ];
    }
}
