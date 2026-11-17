<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use App\Models\School;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileLibraryController extends Controller
{
    /**
     * Handle CORS preflight OPTIONS request.
     */
    public function options()
    {
        return $this->corsResponse([
            'success' => true,
        ], 200);
    }

    /**
     * Endpoint utama Menu Perpustakaan Mobile & PWA:
     * Mengambil Poin Keaktifan, Ringkasan, Daftar Buku yang Dipinjam, dan Riwayat Kunjungan.
     *
     * GET /api/mobile/perpustakaan
     */
    public function myLibrary(Request $request)
    {
        $authResult = $this->authenticateFromAbsensiToken($request);
        if (!$authResult['ok']) {
            return $this->corsResponse([
                'success' => false,
                'message' => $authResult['message'],
            ], $authResult['status']);
        }

        $absenUser = $authResult['user'];
        $resolved = $this->resolveAndSyncMember($absenUser);

        $school = $resolved['school'];
        $member = $resolved['member'];

        $pBorrow = (int) ($school?->point_borrow ?? 10);
        $pVisit = (int) ($school?->point_visit ?? 5);
        $finePerDay = (float) ($school?->fine_per_day ?? 1000);

        // Jika akun belum memiliki profil member (misal admin murni tanpa data guru/siswa)
        if (!$member) {
            return $this->corsResponse([
                'success' => true,
                'message' => 'Data perpustakaan berhasil diambil.',
                'member' => [
                    'id' => null,
                    'name' => $absenUser->full_name ?? $absenUser->username ?? 'Pengguna',
                    'member_code' => $absenUser->username ?? '-',
                    'source_type' => in_array($absenUser->role, ['student', 'siswa']) ? 'siswa' : 'guru',
                    'class_or_dept' => in_array($absenUser->role, ['student', 'siswa']) ? 'Siswa' : 'Guru / Staf',
                    'rfid_uid' => null,
                    'school_id' => $absenUser->school_id,
                    'school_name' => $school?->name ?? '',
                ],
                'points' => [
                    'total_points' => 0,
                    'point_borrow_rate' => $pBorrow,
                    'point_visit_rate' => $pVisit,
                    'total_loans' => 0,
                    'points_from_loans' => 0,
                    'total_visits' => 0,
                    'points_from_visits' => 0,
                    'rank' => null,
                    'total_members' => $school ? Member::where('school_id', $school->id)->count() : 0,
                ],
                'summary' => [
                    'total_loans' => 0,
                    'total_books_qty' => 0,
                    'active_loans' => 0,
                    'active_books_qty' => 0,
                    'dipinjam' => 0,
                    'terlambat' => 0,
                    'kembali' => 0,
                    'total_fine' => 0,
                    'fine_per_day' => $finePerDay,
                ],
                'loans' => [],
                'recent_visits' => [],
            ]);
        }

        $schoolId = $member->school_id;

        // 1. Auto-update status pinjaman yang sudah melewati due_date menjadi 'terlambat'
        $today = Carbon::now()->startOfDay();
        Loan::where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->where('status', 'dipinjam')
            ->whereDate('due_date', '<', $today->format('Y-m-d'))
            ->update(['status' => 'terlambat']);

        // 2. Hitung Poin Keaktifan & Peringkat di Sekolah
        $totalLoansCount = Loan::where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->count();

        $totalVisitsCount = Visit::where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->count();

        $pointsFromLoans = $totalLoansCount * $pBorrow;
        $pointsFromVisits = $totalVisitsCount * $pVisit;
        $totalPoints = $pointsFromLoans + $pointsFromVisits;

        $totalMembersInSchool = Member::where('school_id', $schoolId)->count();
        $rank = null;
        if ($totalPoints > 0) {
            $higherMembersCount = Member::where('school_id', $schoolId)
                ->whereRaw(
                    "((SELECT COUNT(*) FROM loans WHERE loans.member_id = members.id) * ? + (SELECT COUNT(*) FROM visits WHERE visits.member_id = members.id) * ?) > ?",
                    [$pBorrow, $pVisit, $totalPoints]
                )
                ->count();
            $rank = $higherMembersCount + 1;
        }

        // 3. Ambil seluruh pinjaman milik member untuk ringkasan
        $allLoans = Loan::with(['book', 'pickupMember'])
            ->where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->orderBy('borrow_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $dipinjamCount = $allLoans->where('status', 'dipinjam')->count();
        $terlambatCount = $allLoans->where('status', 'terlambat')->count();
        $kembaliCount = $allLoans->where('status', 'kembali')->count();
        $activeLoansCount = $dipinjamCount + $terlambatCount;

        $activeBooksQty = (int) $allLoans->whereIn('status', ['dipinjam', 'terlambat'])->sum(fn($l) => (int) ($l->qty ?? 1));
        $totalBooksQty = (int) $allLoans->sum(fn($l) => (int) ($l->qty ?? 1));

        $totalFine = 0.0;
        foreach ($allLoans as $l) {
            $totalFine += $this->calculateEffectiveFine($l, $finePerDay, $today);
        }

        // 4. Filter daftar pinjaman buku sesuai query parameter
        $statusFilter = strtolower(trim((string) $request->input('status', '')));
        $search = trim((string) $request->input('search', ''));

        $filteredLoans = $allLoans->filter(function (Loan $loan) use ($statusFilter, $search) {
            if ($statusFilter && $statusFilter !== 'all' && $statusFilter !== 'semua') {
                if ($statusFilter === 'active' || $statusFilter === 'aktif') {
                    if (!in_array($loan->status, ['dipinjam', 'terlambat'])) {
                        return false;
                    }
                } elseif ($loan->status !== $statusFilter) {
                    return false;
                }
            }

            if ($search !== '') {
                $needle = mb_strtolower($search);
                $title = mb_strtolower((string) ($loan->book?->title ?? ''));
                $code = mb_strtolower((string) ($loan->book?->code ?? ''));
                $author = mb_strtolower((string) ($loan->book?->author ?? ''));
                $publisher = mb_strtolower((string) ($loan->book?->publisher ?? ''));

                if (
                    !str_contains($title, $needle) &&
                    !str_contains($code, $needle) &&
                    !str_contains($author, $needle) &&
                    !str_contains($publisher, $needle)
                ) {
                    return false;
                }
            }

            return true;
        })->values();

        // 5. Ambil riwayat kunjungan terbaru
        $recentVisits = Visit::where('school_id', $schoolId)
            ->where('member_id', $member->id)
            ->orderBy('scanned_at', 'desc')
            ->limit(15)
            ->get()
            ->map(function (Visit $v) use ($pVisit) {
                $dt = $v->scanned_at ? Carbon::parse($v->scanned_at) : null;
                return [
                    'id' => $v->id,
                    'purpose' => $v->purpose ?: 'Kunjungan Perpustakaan',
                    'scanned_at' => $dt?->toIso8601String(),
                    'tanggal' => $dt?->format('Y-m-d'),
                    'waktu' => $dt?->format('H:i'),
                    'poin' => $pVisit,
                ];
            })->values();

        return $this->corsResponse([
            'success' => true,
            'message' => 'Data perpustakaan berhasil diambil.',
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'member_code' => $member->member_code,
                'source_type' => $member->source_type,
                'class_or_dept' => $member->class_or_dept,
                'rfid_uid' => $member->rfid_uid,
                'school_id' => $member->school_id,
                'school_name' => $school?->name ?? '',
            ],
            'points' => [
                'total_points' => $totalPoints,
                'point_borrow_rate' => $pBorrow,
                'point_visit_rate' => $pVisit,
                'total_loans' => $totalLoansCount,
                'points_from_loans' => $pointsFromLoans,
                'total_visits' => $totalVisitsCount,
                'points_from_visits' => $pointsFromVisits,
                'rank' => $rank,
                'total_members' => $totalMembersInSchool,
            ],
            'summary' => [
                'total_loans' => $allLoans->count(),
                'total_books_qty' => $totalBooksQty,
                'active_loans' => $activeLoansCount,
                'active_books_qty' => $activeBooksQty,
                'dipinjam' => $dipinjamCount,
                'terlambat' => $terlambatCount,
                'kembali' => $kembaliCount,
                'total_fine' => (int) round($totalFine),
                'fine_per_day' => (int) round($finePerDay),
            ],
            'loans' => $filteredLoans->map(fn(Loan $l) => $this->formatLoan($l, $finePerDay, $today, $pBorrow, $request))->values(),
            'recent_visits' => $recentVisits,
        ]);
    }

    /**
     * Verifikasi Bearer Token Sanctum terhadap database Absensi (mysql_absensi).
     */
    private function authenticateFromAbsensiToken(Request $request): array
    {
        $bearer = $request->bearerToken();
        if (!$bearer) {
            return [
                'ok' => false,
                'status' => 401,
                'message' => 'Token autentikasi tidak ditemukan. Silakan login kembali.',
            ];
        }

        try {
            $conn = DB::connection('mysql_absensi');
            $tokenRecord = null;

            if (str_contains($bearer, '|')) {
                [$tokenId, $plainTextToken] = explode('|', $bearer, 2);
                $record = $conn->table('personal_access_tokens')
                    ->where('id', (int) $tokenId)
                    ->first();

                if ($record && hash_equals($record->token, hash('sha256', $plainTextToken))) {
                    $tokenRecord = $record;
                }
            } else {
                $tokenRecord = $conn->table('personal_access_tokens')
                    ->where('token', hash('sha256', $bearer))
                    ->first();
            }

            if (!$tokenRecord) {
                return [
                    'ok' => false,
                    'status' => 401,
                    'message' => 'Sesi login tidak valid atau telah berakhir.',
                ];
            }

            if (!empty($tokenRecord->expires_at) && Carbon::parse($tokenRecord->expires_at)->isPast()) {
                return [
                    'ok' => false,
                    'status' => 401,
                    'message' => 'Token autentikasi telah kedaluwarsa.',
                ];
            }

            $user = $conn->table('users')
                ->where('id', $tokenRecord->tokenable_id)
                ->first();

            if (!$user) {
                return [
                    'ok' => false,
                    'status' => 401,
                    'message' => 'Data pengguna tidak ditemukan.',
                ];
            }

            // Update last_used_at secara non-blocking
            try {
                $conn->table('personal_access_tokens')
                    ->where('id', $tokenRecord->id)
                    ->update(['last_used_at' => now()]);
            } catch (\Throwable $e) {
                // Ignore
            }

            return [
                'ok' => true,
                'status' => 200,
                'user' => $user,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 500,
                'message' => 'Gagal memverifikasi sesi ke server autentikasi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mencari atau menyinkronkan otomatis data School & Member (Siswa/Guru) di e-lib
     * berdasarkan akun user dari database absensi.
     */
    private function resolveAndSyncMember(object $absenUser): array
    {
        $conn = DB::connection('mysql_absensi');
        $role = strtolower(trim((string) ($absenUser->role ?? '')));

        // 1. Cek apakah user ini adalah Siswa
        $siswa = null;
        if (Schema::connection('mysql_absensi')->hasColumn('siswa', 'user_id')) {
            $siswa = $conn->table('siswa')
                ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->select('siswa.*', 'kelas.nama_kelas')
                ->where('siswa.user_id', $absenUser->id)
                ->first();
        }

        if (!$siswa && in_array($role, ['student', 'siswa'])) {
            $cleanNis = str_replace('siswa_', '', (string) ($absenUser->username ?? ''));
            if ($cleanNis !== '') {
                $siswa = $conn->table('siswa')
                    ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                    ->select('siswa.*', 'kelas.nama_kelas')
                    ->where('siswa.nis', $cleanNis)
                    ->first();
            }
        }

        if ($siswa) {
            $school = $this->ensureSchoolExists($conn, $siswa->school_id);
            $member = Member::updateOrCreate(
                [
                    'school_id' => $siswa->school_id,
                    'source_type' => 'siswa',
                    'source_id' => $siswa->id,
                ],
                [
                    'member_code' => $siswa->nis ?? ('S-' . $siswa->id),
                    'name' => $siswa->nama,
                    'class_or_dept' => $siswa->nama_kelas ?? 'Siswa',
                    'rfid_uid' => !empty($siswa->uid_rfid) ? strtoupper(trim($siswa->uid_rfid)) : null,
                ]
            );

            return [
                'school' => $school,
                'member' => $member,
            ];
        }

        // 2. Cek apakah user ini adalah Guru / Staf
        $guru = null;
        if (Schema::connection('mysql_absensi')->hasColumn('guru', 'user_id')) {
            $guru = $conn->table('guru')
                ->where('user_id', $absenUser->id)
                ->first();
        }

        if (!$guru && !empty($absenUser->username)) {
            $guru = $conn->table('guru')
                ->where('nip', $absenUser->username)
                ->when($absenUser->school_id, fn($q) => $q->where('school_id', $absenUser->school_id))
                ->first();
        }

        if (!$guru && !empty($absenUser->full_name) && $absenUser->school_id) {
            $guru = $conn->table('guru')
                ->where('school_id', $absenUser->school_id)
                ->where('nama', $absenUser->full_name)
                ->first();
        }

        if ($guru) {
            $school = $this->ensureSchoolExists($conn, $guru->school_id);
            $member = Member::updateOrCreate(
                [
                    'school_id' => $guru->school_id,
                    'source_type' => 'guru',
                    'source_id' => $guru->id,
                ],
                [
                    'member_code' => $guru->nip ?: ('G-' . $guru->id),
                    'name' => $guru->nama,
                    'class_or_dept' => 'Guru / Staf',
                    'rfid_uid' => !empty($guru->uid_rfid) ? strtoupper(trim($guru->uid_rfid)) : null,
                ]
            );

            return [
                'school' => $school,
                'member' => $member,
            ];
        }

        // 3. Fallback: cari di tabel members e-lib berdasarkan school_id + nama/username
        $school = $absenUser->school_id ? $this->ensureSchoolExists($conn, $absenUser->school_id) : null;
        $member = null;
        if ($absenUser->school_id) {
            $member = Member::where('school_id', $absenUser->school_id)
                ->where(function ($q) use ($absenUser) {
                    if (!empty($absenUser->username)) {
                        $q->orWhere('member_code', $absenUser->username);
                    }
                    if (!empty($absenUser->full_name)) {
                        $q->orWhere('name', $absenUser->full_name);
                    }
                })
                ->first();
        }

        return [
            'school' => $school,
            'member' => $member,
        ];
    }

    /**
     * Pastikan data sekolah sudah ada di tabel schools e-lib.
     */
    private function ensureSchoolExists($conn, $schoolId): ?School
    {
        if (!$schoolId) {
            return null;
        }

        $school = School::find($schoolId);
        if ($school) {
            return $school;
        }

        $absenSchool = $conn->table('schools')->where('id', $schoolId)->first();
        return School::create([
            'id' => $schoolId,
            'name' => $absenSchool->name ?? ('Sekolah #' . $schoolId),
            'point_borrow' => 10,
            'point_visit' => 5,
            'fine_per_day' => 1000.00,
            'is_perpus_active' => true,
        ]);
    }

    /**
     * Hitung denda efektif (denda tercatat jika sudah kembali, atau estimasi denda berjalan jika terlambat).
     */
    private function calculateEffectiveFine(Loan $loan, float $finePerDay, Carbon $today): float
    {
        if ($loan->status === 'kembali') {
            return (float) ($loan->fine ?? 0);
        }

        if ($loan->due_date) {
            $dueDate = Carbon::parse($loan->due_date)->startOfDay();
            if ($today->gt($dueDate)) {
                $daysOverdue = (int) abs($today->diffInDays($dueDate));
                return (float) ($daysOverdue * $finePerDay);
            }
        }

        return (float) ($loan->fine ?? 0);
    }

    /**
     * Format item peminjaman buku untuk JSON response.
     */
    private function formatLoan(Loan $loan, float $finePerDay, Carbon $today, int $pBorrow, Request $request): array
    {
        $borrowDate = $loan->borrow_date ? Carbon::parse($loan->borrow_date)->startOfDay() : null;
        $dueDate = $loan->due_date ? Carbon::parse($loan->due_date)->startOfDay() : null;
        $returnDate = $loan->return_date ? Carbon::parse($loan->return_date)->startOfDay() : null;

        $daysRemaining = 0;
        $daysOverdue = 0;

        if ($loan->status === 'kembali') {
            if ($dueDate && $returnDate && $returnDate->gt($dueDate)) {
                $daysOverdue = (int) abs($returnDate->diffInDays($dueDate));
            }
        } elseif ($dueDate) {
            if ($today->gt($dueDate)) {
                $daysOverdue = (int) abs($today->diffInDays($dueDate));
            } else {
                $daysRemaining = (int) abs($dueDate->diffInDays($today));
            }
        }

        $statusLabel = match ($loan->status) {
            'kembali' => 'Sudah Kembali',
            'terlambat' => 'Terlambat',
            default => 'Dipinjam',
        };

        $effectiveFine = $this->calculateEffectiveFine($loan, $finePerDay, $today);

        $coverUrl = $loan->book?->cover_url;
        if (!empty($coverUrl) && !str_starts_with($coverUrl, 'http://') && !str_starts_with($coverUrl, 'https://')) {
            $base = rtrim($request->getSchemeAndHttpHost(), '/');
            $coverUrl = $base . '/' . ltrim($coverUrl, '/');
        }

        return [
            'id' => $loan->id,
            'status' => $loan->status,
            'status_label' => $statusLabel,
            'qty' => (int) ($loan->qty ?? 1),
            'borrow_date' => $borrowDate?->format('Y-m-d'),
            'due_date' => $dueDate?->format('Y-m-d'),
            'return_date' => $returnDate?->format('Y-m-d'),
            'days_remaining' => $daysRemaining,
            'days_overdue' => $daysOverdue,
            'is_overdue' => $loan->status === 'terlambat' || $daysOverdue > 0,
            'fine' => (int) round($effectiveFine),
            'poin' => $pBorrow,
            'pickup_member' => $loan->pickupMember ? [
                'id' => $loan->pickupMember->id,
                'name' => $loan->pickupMember->name,
                'member_code' => $loan->pickupMember->member_code,
                'class_or_dept' => $loan->pickupMember->class_or_dept,
            ] : null,
            'book' => $loan->book ? [
                'id' => $loan->book->id,
                'code' => $loan->book->code,
                'title' => $loan->book->title,
                'author' => $loan->book->author,
                'publisher' => $loan->book->publisher,
                'year' => $loan->book->year ? (int) $loan->book->year : null,
                'location' => $loan->book->location,
                'cover_url' => $coverUrl,
            ] : null,
        ];
    }

    /**
     * Helper JSON response dengan header CORS agar dapat diakses langsung dari PWA & Mobile.
     */
    private function corsResponse(array $data, int $status = 200)
    {
        return response()->json($data, $status)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With');
    }
}
