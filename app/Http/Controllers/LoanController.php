<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Book;
use App\Models\Member;
use App\Models\Device;
use App\Models\PendingVerification;
use App\Models\Visit;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Daftar Peminjaman Buku.
     */
    public function index(Request $request)
    {
        // Auto-fix enum status column if 'completed' is not supported in the database enum
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pending_verifications MODIFY COLUMN status ENUM('pending', 'verified', 'failed', 'expired', 'completed') DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Silence
        }

        $schoolId = auth()->user()->school_id;
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Loan::with(['member', 'book'])
            ->where('school_id', $schoolId)
            ->orderBy('borrow_date', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('member', function($m) use ($search) {
                    $m->where('name', 'like', "%{$search}%")
                      ->orWhere('member_code', 'like', "%{$search}%");
                })->orWhereHas('book', function($b) use ($search) {
                    $b->where('title', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            });
        }

        $loans = $query->paginate(15);

        // Update status terlambat jika melewati due_date dan belum kembali
        foreach ($loans as $loan) {
            if ($loan->status === 'dipinjam' && Carbon::now()->startOfDay()->gt($loan->due_date)) {
                $loan->update(['status' => 'terlambat']);
            }
        }

        // Ambil data untuk modal peminjaman baru
        $books = Book::where('school_id', $schoolId)->where('sisa_stok', '>', 0)->orderBy('title', 'asc')->get();
        $devices = Device::where('school_id', $schoolId)
            ->where('type', 'rfid_perpus_pinjam')
            ->where('active', true)
            ->get();
        $members = Member::where('school_id', $schoolId)->orderBy('name', 'asc')->get();

        return view('perpus.loan.index', compact('loans', 'search', 'status', 'books', 'devices', 'members'));
    }



    /**
     * Langkah 1: Memulai sesi verifikasi RFID (Menunggu Tap Kartu)
     */
    public function startVerification(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'exists:books,id',
            'device_id' => 'required|exists:devices,id',
        ]);

        $device = Device::findOrFail($request->device_id);
        if ($device->school_id !== $schoolId) {
            return response()->json(['success' => false, 'message' => 'Device tidak valid.'], 403);
        }

        // Hapus verification pending lama untuk device ini
        PendingVerification::where('device_id', $device->id)->update(['status' => 'expired']);

        // Simpan data transaksi ke tabel pending verifications
        $pending = PendingVerification::create([
            'school_id' => $schoolId,
            'device_id' => $device->id,
            'transaction_data' => [
                'book_ids' => $request->book_ids,
                'borrow_date' => Carbon::now()->format('Y-m-d'),
                'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'), // Default 7 hari pinjam
            ],
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(2) // Kedaluwarsa dalam 2 menit
        ]);

        return response()->json([
            'success' => true,
            'pending_id' => $pending->id,
            'expires_in' => 120 // detik
        ]);
    }

    /**
     * Langkah 2: Polling status scan oleh Halaman Web
     */
    /**
     * Langkah 2: Polling status scan oleh Halaman Web
     */
    public function checkScanStatus($id)
    {
        $pending = PendingVerification::findOrFail($id);
        $schoolId = auth()->user()->school_id;

        if ($pending->school_id !== $schoolId) {
            return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 403);
        }

        // Cek apakah sudah kedaluwarsa secara waktu
        if ($pending->status === 'pending' && Carbon::now()->gt($pending->expires_at)) {
            $pending->update(['status' => 'expired']);
        }

        if ($pending->status === 'verified') {
            $member = Member::where('rfid_uid', $pending->scanned_uid)
                ->where('school_id', $schoolId)
                ->first();

            if (!$member) {
                return response()->json(['status' => 'failed', 'message' => 'Anggota tidak ditemukan.'], 404);
            }

            return response()->json([
                'status' => 'scanned',
                'member_id' => $member->id,
                'member_name' => $member->name,
                'member_code' => $member->member_code,
                'class_or_dept' => $member->class_or_dept ?? 'Guru / Staf',
                'scanned_uid' => $pending->scanned_uid
            ]);
        }

        if ($pending->status === 'completed') {
            return response()->json([
                'status' => 'completed',
                'message' => 'Peminjaman telah diproses.'
            ]);
        }

        if ($pending->status === 'failed') {
            return response()->json([
                'status' => 'failed',
                'message' => $pending->error_message ?? 'Verifikasi tap gagal.'
            ]);
        }

        if ($pending->status === 'expired') {
            return response()->json([
                'status' => 'expired',
                'message' => 'Waktu tap kartu telah habis. Silakan coba lagi.'
            ]);
        }

        return response()->json(['status' => 'pending']);
    }

    /**
     * Langkah 3: Konfirmasi peminjaman oleh admin setelah scan kartu sukses
     */
    public function confirmVerification(Request $request)
    {
        // Auto-fix enum status column if 'completed' is not supported in the database enum
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pending_verifications MODIFY COLUMN status ENUM('pending', 'verified', 'failed', 'expired', 'completed') DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Silence
        }

        $schoolId = auth()->user()->school_id;

        $request->validate([
            'pending_id' => 'required|exists:pending_verifications,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:borrow_date',
        ]);

        $pending = PendingVerification::findOrFail($request->pending_id);
        if ($pending->school_id !== $schoolId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($pending->status !== 'verified') {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak dalam status verifikasi.'], 422);
        }

        $member = Member::where('rfid_uid', $pending->scanned_uid)
            ->where('school_id', $schoolId)
            ->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Anggota tidak ditemukan.'], 404);
        }

        $txData = $pending->transaction_data;
        $createdLoans = [];

        foreach ($txData['book_ids'] as $bookId) {
            $book = Book::find($bookId);
            if ($book && $book->sisa_stok > 0) {
                $loan = Loan::create([
                    'school_id' => $schoolId,
                    'member_id' => $member->id,
                    'book_id' => $book->id,
                    'borrow_date' => Carbon::parse($request->borrow_date)->format('Y-m-d'),
                    'due_date' => Carbon::parse($request->due_date)->format('Y-m-d'),
                    'status' => 'dipinjam',
                    'fine' => 0.00
                ]);

                $book->decrement('sisa_stok');
                $createdLoans[] = $loan;
            }
        }

        $pending->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'member_name' => $member->name,
            'total_books' => count($createdLoans)
        ]);
    }

    /**
     * Transaksi Pengembalian Buku & Perhitungan Denda.
     */
    public function returnBook(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);
        $schoolId = auth()->user()->school_id;

        if ($loan->school_id !== $schoolId) {
            abort(403);
        }

        if ($loan->status === 'kembali') {
            return redirect()->back()->with('error', 'Buku ini sudah dikembalikan sebelumnya.');
        }

        $request->validate([
            'return_date' => 'required|date',
        ]);

        $returnDate = Carbon::parse($request->return_date)->startOfDay();
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();
        
        $fine = 0.00;
        // Hitung denda jika terlambat (Rp 1.000,- per hari)
        if ($returnDate->gt($dueDate)) {
            $daysLate = $returnDate->diffInDays($dueDate);
            $fine = $daysLate * 1000.00;
        }

        // Simpan data pengembalian
        $loan->update([
            'return_date' => $returnDate->format('Y-m-d'),
            'status' => 'kembali',
            'fine' => $fine
        ]);

        // Tambah sisa stok buku kembali
        $loan->book->increment('sisa_stok');

        $msg = 'Buku berhasil dikembalikan.';
        if ($fine > 0) {
            $msg .= ' Terlambat ' . $returnDate->diffInDays($dueDate) . ' hari. Denda: Rp ' . number_format($fine, 0, ',', '.');
        }

        return redirect()->route('perpus.loan.index')->with('success', $msg);
    }

    /**
     * Langkah Alternatif: Peminjaman Manual Tanpa RFID (Jika RFID Rusak)
     */
    public function storeManual(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'book_ids' => 'required|array|min:1',
            'book_ids.*' => 'exists:books,id',
            'member_id' => 'required|exists:members,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:borrow_date',
        ]);

        $member = Member::findOrFail($request->member_id);
        if ($member->school_id !== $schoolId) {
            return response()->json(['success' => false, 'message' => 'Anggota tidak valid.'], 403);
        }

        // VALIDASI: Apakah anggota sudah tap kunjungan hari ini?
        $hasVisitToday = Visit::where('member_id', $member->id)
            ->where('school_id', $schoolId)
            ->whereDate('scanned_at', today())
            ->exists();

        if (!$hasVisitToday) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota belum melakukan tap kunjungan hari ini (Belum tercatat di Buku Tamu Kunjungan).'
            ], 422);
        }

        $createdLoans = [];
        foreach ($request->book_ids as $bookId) {
            $book = Book::find($bookId);
            if ($book && $book->sisa_stok > 0) {
                $loan = Loan::create([
                    'school_id' => $schoolId,
                    'member_id' => $member->id,
                    'book_id' => $book->id,
                    'borrow_date' => Carbon::parse($request->borrow_date)->format('Y-m-d'),
                    'due_date' => Carbon::parse($request->due_date)->format('Y-m-d'),
                    'status' => 'dipinjam',
                    'fine' => 0.00
                ]);
                $book->decrement('sisa_stok');
                $createdLoans[] = $loan;
            }
        }

        return response()->json([
            'success' => true,
            'member_name' => $member->name,
            'total_books' => count($createdLoans)
        ]);
    }

    /**
     * Inisiasi scan RFID untuk pengembalian buku (return lookup)
     */
    public function startReturnVerification(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'device_id' => 'required|exists:devices,id',
        ]);

        $device = Device::findOrFail($request->device_id);
        if ($device->school_id !== $schoolId) {
            return response()->json(['success' => false, 'message' => 'Device tidak valid.'], 403);
        }

        // Hapus verification pending lama untuk device ini
        PendingVerification::where('device_id', $device->id)->update(['status' => 'expired']);

        // Simpan data transaksi ke tabel pending verifications dengan type = return_lookup
        $pending = PendingVerification::create([
            'school_id' => $schoolId,
            'device_id' => $device->id,
            'transaction_data' => [
                'type' => 'return_lookup'
            ],
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(2) // Kedaluwarsa dalam 2 menit
        ]);

        return response()->json([
            'success' => true,
            'pending_id' => $pending->id,
            'expires_in' => 120 // detik
        ]);
    }

    /**
     * Polling status scan untuk pengembalian buku (return lookup)
     */
    public function checkReturnStatus($id)
    {
        $pending = PendingVerification::findOrFail($id);
        $schoolId = auth()->user()->school_id;

        if ($pending->school_id !== $schoolId) {
            return response()->json(['status' => 'failed', 'message' => 'Unauthorized'], 403);
        }

        // Cek apakah sudah kedaluwarsa secara waktu
        if ($pending->status === 'pending' && Carbon::now()->gt($pending->expires_at)) {
            $pending->update(['status' => 'expired']);
        }

        if ($pending->status === 'verified') {
            $member = Member::where('rfid_uid', $pending->scanned_uid)
                ->where('school_id', $schoolId)
                ->first();

            if (!$member) {
                return response()->json(['status' => 'failed', 'message' => 'Anggota tidak ditemukan.'], 404);
            }

            // Ambil semua peminjaman aktif untuk anggota ini
            $activeLoans = Loan::with('book')
                ->where('member_id', $member->id)
                ->where('school_id', $schoolId)
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->get()
                ->map(function ($loan) {
                    return [
                        'id' => $loan->id,
                        'book_title' => $loan->book->title,
                        'borrow_date' => $loan->borrow_date->format('Y-m-d'),
                        'due_date' => $loan->due_date->format('Y-m-d'),
                        'member_name' => $loan->member->name ?? ''
                    ];
                });

            return response()->json([
                'status' => 'verified',
                'member_name' => $member->name,
                'loans' => $activeLoans
            ]);
        }

        if ($pending->status === 'failed') {
            return response()->json([
                'status' => 'failed',
                'message' => $pending->error_message ?? 'Scan kartu gagal.'
            ]);
        }

        if ($pending->status === 'expired') {
            return response()->json([
                'status' => 'expired',
                'message' => 'Waktu scan habis. Silakan coba lagi.'
            ]);
        }

        return response()->json(['status' => 'pending']);
    }
}
