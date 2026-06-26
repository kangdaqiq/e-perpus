<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Visit;
use App\Models\Member;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Halaman Dashboard Utama.
     */
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        if (!$schoolId) {
            // Untuk super admin jika login tanpa school_id
            $totalSchools = \App\Models\School::count();
            $totalUsers = \App\Models\User::count();
            $totalMembers = \App\Models\Member::count();
            $totalBooks = \App\Models\Book::sum('stock') ?? 0;
            $activeLoans = \App\Models\Loan::whereIn('status', ['dipinjam', 'terlambat'])->count();
            $todayVisits = \App\Models\Visit::whereDate('scanned_at', Carbon::today())->count();

            // Fetch school-wise summaries
            $schools = \App\Models\School::withCount([
                'users',
                'members',
                'visits as today_visits_count' => function ($query) {
                    $query->whereDate('scanned_at', Carbon::today());
                },
                'loans as active_loans_count' => function ($query) {
                    $query->whereIn('status', ['dipinjam', 'terlambat']);
                }
            ])->get();

            // Calculate total books per school
            foreach ($schools as $school) {
                $school->books_count = \App\Models\Book::where('school_id', $school->id)->sum('stock') ?? 0;
            }

            return view('perpus.dashboard_super', compact(
                'totalSchools',
                'totalUsers',
                'totalMembers',
                'totalBooks',
                'activeLoans',
                'todayVisits',
                'schools'
            ));
        }

        // Statistik
        $totalBooks = Book::where('school_id', $schoolId)->sum('stock');
        $activeLoans = Loan::where('school_id', $schoolId)->whereIn('status', ['dipinjam', 'terlambat'])->count();
        $overdueLoans = Loan::where('school_id', $schoolId)->where('status', 'terlambat')->count();
        $todayVisits = Visit::where('school_id', $schoolId)->whereDate('scanned_at', Carbon::today())->count();
        $totalFines = Loan::where('school_id', $schoolId)->sum('fine');
        $totalMembers = Member::where('school_id', $schoolId)->count();

        // Riwayat transaksi terbaru
        $recentLoans = Loan::with(['member', 'book'])
            ->where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentVisits = Visit::with('member')
            ->where('school_id', $schoolId)
            ->orderBy('scanned_at', 'desc')
            ->limit(5)
            ->get();

        // Buku Favorit (Peminjaman Terbanyak)
        $favoriteBook = Book::where('school_id', $schoolId)
            ->withCount('loans')
            ->orderBy('loans_count', 'desc')
            ->first();

        // Kunjungan Terbanyak
        $topVisitor = Member::where('school_id', $schoolId)
            ->withCount('visits')
            ->orderBy('visits_count', 'desc')
            ->first();

        // Peminjam Terbanyak
        $topBorrower = Member::where('school_id', $schoolId)
            ->withCount('loans')
            ->orderBy('loans_count', 'desc')
            ->first();

        $school = auth()->user()->school;
        $pBorrow = $school->point_borrow ?? 10;
        $pVisit = $school->point_visit ?? 5;

        // Papan Peringkat Keaktifan
        $topMembers = Member::where('school_id', $schoolId)
            ->withCount(['loans', 'visits'])
            ->orderByRaw("((SELECT COUNT(*) FROM loans WHERE loans.member_id = members.id) * {$pBorrow} + (SELECT COUNT(*) FROM visits WHERE visits.member_id = members.id) * {$pVisit}) DESC")
            ->limit(5)
            ->get()
            ->map(function ($member) use ($pBorrow, $pVisit) {
                $member->points = ($member->loans_count * $pBorrow) + ($member->visits_count * $pVisit);
                return $member;
            })
            ->filter(function ($member) {
                return $member->points > 0;
            });

        return view('perpus.dashboard', compact(
            'school',
            'totalBooks',
            'activeLoans',
            'overdueLoans',
            'todayVisits',
            'totalFines',
            'totalMembers',
            'recentLoans',
            'recentVisits',
            'favoriteBook',
            'topVisitor',
            'topBorrower',
            'topMembers'
        ));
    }
}
