<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Loan;
use App\Models\Member;
use App\Models\Book;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Helper untuk mengambil opsi Kelas dan Jurusan dari data anggota sekolah.
     */
    private function getClassAndDeptOptions($schoolId)
    {
        $allClasses = Member::where('school_id', $schoolId)
            ->whereNotNull('class_or_dept')
            ->pluck('class_or_dept')
            ->unique()
            ->values();

        $kelasList = collect(['X', 'XI', 'XII']);
        $jurusanList = collect();

        foreach ($allClasses as $item) {
            $itemTrimmed = trim($item);
            // Ekstrak jurusan dari string seperti "X RPL 1", "XI TKJ 2", "XII MM"
            $parts = explode(' ', $itemTrimmed);
            if (count($parts) >= 2) {
                // Ambil kata kedua sebagai nama jurusan (misal RPL, TKJ, DKV, MM, AKL)
                $jurusan = strtoupper($parts[1]);
                if (strlen($jurusan) >= 2 && !in_array($jurusan, ['GURU', 'STAF', 'GURU/STAF'])) {
                    $jurusanList->push($jurusan);
                }
            } else if (!in_array(strtoupper($itemTrimmed), ['GURU', 'STAF', 'GURU / STAF'])) {
                $jurusanList->push(strtoupper($itemTrimmed));
            }
        }

        return [
            'kelasList' => $kelasList->unique()->values(),
            'jurusanList' => $jurusanList->unique()->values(),
            'rawClasses' => $allClasses,
        ];
    }

    /**
     * Rekapitulasi Data Kunjungan.
     */
    public function rekapKunjungan(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = auth()->user()->school;

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $kelas = $request->input('kelas');
        $jurusan = $request->input('jurusan');
        $search = $request->input('search');

        $options = $this->getClassAndDeptOptions($schoolId);
        $kelasList = $options['kelasList'];
        $jurusanList = $options['jurusanList'];

        $query = Visit::with('member')
            ->where('school_id', $schoolId)
            ->whereDate('scanned_at', '>=', $startDate)
            ->whereDate('scanned_at', '<=', $endDate);

        if ($kelas) {
            $query->where(function($q) use ($kelas) {
                $q->whereHas('member', function($m) use ($kelas) {
                    $m->where('class_or_dept', 'LIKE', "{$kelas} %")
                      ->orWhere('class_or_dept', 'LIKE', "{$kelas}-%")
                      ->orWhere('class_or_dept', '=', $kelas);
                })->orWhere('class_or_dept', 'LIKE', "{$kelas} %")
                  ->orWhere('class_or_dept', '=', $kelas);
            });
        }

        if ($jurusan) {
            $query->where(function($q) use ($jurusan) {
                $q->whereHas('member', function($m) use ($jurusan) {
                    $m->where('class_or_dept', 'LIKE', "%{$jurusan}%");
                })->orWhere('class_or_dept', 'LIKE', "%{$jurusan}%");
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('visitor_name', 'LIKE', "%{$search}%")
                  ->orWhere('purpose', 'LIKE', "%{$search}%")
                  ->orWhereHas('member', function($m) use ($search) {
                      $m->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('member_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $allVisits = (clone $query)->orderBy('scanned_at', 'desc')->get();

        // Summary metrics
        $totalVisits = $allVisits->count();
        $studentVisits = $allVisits->filter(function($v) {
            return ($v->member && $v->member->source_type === 'siswa') || ($v->class_or_dept && !str_contains(strtolower($v->class_or_dept), 'guru'));
        })->count();
        $teacherVisits = $allVisits->filter(function($v) {
            return ($v->member && $v->member->source_type === 'guru') || ($v->class_or_dept && str_contains(strtolower($v->class_or_dept), 'guru'));
        })->count();
        $guestVisits = $allVisits->filter(function($v) {
            return !$v->member_id;
        })->count();

        $visits = (clone $query)->orderBy('scanned_at', 'desc')->paginate(20)->withQueryString();

        return view('perpus.reports.visits', compact(
            'school',
            'visits',
            'startDate',
            'endDate',
            'kelas',
            'jurusan',
            'search',
            'kelasList',
            'jurusanList',
            'totalVisits',
            'studentVisits',
            'teacherVisits',
            'guestVisits'
        ));
    }

    /**
     * Cetak Halaman Rekapitulasi Kunjungan.
     */
    public function cetakKunjungan(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = auth()->user()->school;

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $kelas = $request->input('kelas');
        $jurusan = $request->input('jurusan');
        $search = $request->input('search');

        $query = Visit::with('member')
            ->where('school_id', $schoolId)
            ->whereDate('scanned_at', '>=', $startDate)
            ->whereDate('scanned_at', '<=', $endDate);

        if ($kelas) {
            $query->where(function($q) use ($kelas) {
                $q->whereHas('member', function($m) use ($kelas) {
                    $m->where('class_or_dept', 'LIKE', "{$kelas} %")
                      ->orWhere('class_or_dept', 'LIKE', "{$kelas}-%")
                      ->orWhere('class_or_dept', '=', $kelas);
                })->orWhere('class_or_dept', 'LIKE', "{$kelas} %")
                  ->orWhere('class_or_dept', '=', $kelas);
            });
        }

        if ($jurusan) {
            $query->where(function($q) use ($jurusan) {
                $q->whereHas('member', function($m) use ($jurusan) {
                    $m->where('class_or_dept', 'LIKE', "%{$jurusan}%");
                })->orWhere('class_or_dept', 'LIKE', "%{$jurusan}%");
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('visitor_name', 'LIKE', "%{$search}%")
                  ->orWhere('purpose', 'LIKE', "%{$search}%")
                  ->orWhereHas('member', function($m) use ($search) {
                      $m->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('member_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $visits = $query->orderBy('scanned_at', 'desc')->get();

        return view('perpus.reports.visits_print', compact(
            'school',
            'visits',
            'startDate',
            'endDate',
            'kelas',
            'jurusan',
            'search'
        ));
    }

    /**
     * Rekapitulasi Data Peminjaman.
     */
    public function rekapPeminjaman(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = auth()->user()->school;

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $kelas = $request->input('kelas');
        $jurusan = $request->input('jurusan');
        $search = $request->input('search');

        $options = $this->getClassAndDeptOptions($schoolId);
        $kelasList = $options['kelasList'];
        $jurusanList = $options['jurusanList'];

        $query = Loan::with(['member', 'book', 'pickupMember'])
            ->where('school_id', $schoolId)
            ->whereDate('borrow_date', '>=', $startDate)
            ->whereDate('borrow_date', '<=', $endDate);

        if ($status) {
            $query->where('status', $status);
        }

        if ($kelas) {
            $query->whereHas('member', function($m) use ($kelas) {
                $m->where('class_or_dept', 'LIKE', "{$kelas} %")
                  ->orWhere('class_or_dept', 'LIKE', "{$kelas}-%")
                  ->orWhere('class_or_dept', '=', $kelas);
            });
        }

        if ($jurusan) {
            $query->whereHas('member', function($m) use ($jurusan) {
                $m->where('class_or_dept', 'LIKE', "%{$jurusan}%");
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('member', function($m) use ($search) {
                    $m->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('member_code', 'LIKE', "%{$search}%");
                })->orWhereHas('book', function($b) use ($search) {
                    $b->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%");
                });
            });
        }

        $allLoans = (clone $query)->get();

        // Summary metrics
        $totalTransactions = $allLoans->count();
        $totalQty = $allLoans->sum('qty');
        $borrowedCount = $allLoans->where('status', 'dipinjam')->count();
        $returnedCount = $allLoans->where('status', 'kembali')->count();
        $overdueCount = $allLoans->where('status', 'terlambat')->count();
        $totalFines = $allLoans->sum('fine');

        $loans = (clone $query)->orderBy('borrow_date', 'desc')->paginate(20)->withQueryString();

        return view('perpus.reports.loans', compact(
            'school',
            'loans',
            'startDate',
            'endDate',
            'status',
            'kelas',
            'jurusan',
            'search',
            'kelasList',
            'jurusanList',
            'totalTransactions',
            'totalQty',
            'borrowedCount',
            'returnedCount',
            'overdueCount',
            'totalFines'
        ));
    }

    /**
     * Cetak Halaman Rekapitulasi Peminjaman.
     */
    public function cetakPeminjaman(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = auth()->user()->school;

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $status = $request->input('status');
        $kelas = $request->input('kelas');
        $jurusan = $request->input('jurusan');
        $search = $request->input('search');

        $query = Loan::with(['member', 'book', 'pickupMember'])
            ->where('school_id', $schoolId)
            ->whereDate('borrow_date', '>=', $startDate)
            ->whereDate('borrow_date', '<=', $endDate);

        if ($status) {
            $query->where('status', $status);
        }

        if ($kelas) {
            $query->whereHas('member', function($m) use ($kelas) {
                $m->where('class_or_dept', 'LIKE', "{$kelas} %")
                  ->orWhere('class_or_dept', 'LIKE', "{$kelas}-%")
                  ->orWhere('class_or_dept', '=', $kelas);
            });
        }

        if ($jurusan) {
            $query->whereHas('member', function($m) use ($jurusan) {
                $m->where('class_or_dept', 'LIKE', "%{$jurusan}%");
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('member', function($m) use ($search) {
                    $m->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('member_code', 'LIKE', "%{$search}%");
                })->orWhereHas('book', function($b) use ($search) {
                    $b->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%");
                });
            });
        }

        $loans = $query->orderBy('borrow_date', 'desc')->get();

        return view('perpus.reports.loans_print', compact(
            'school',
            'loans',
            'startDate',
            'endDate',
            'status',
            'kelas',
            'jurusan',
            'search'
        ));
    }
}
