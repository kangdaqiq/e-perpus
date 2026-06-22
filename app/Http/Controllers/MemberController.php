<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    /**
     * Tampilkan daftar anggota perpustakaan (Siswa & Guru).
     */
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $search = $request->input('search');
        $type = $request->input('type'); // 'siswa', 'guru', or null (all)

        $query = Member::where('school_id', $schoolId);

        if ($type) {
            $query->where('source_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('member_code', 'like', "%{$search}%")
                  ->orWhere('class_or_dept', 'like', "%{$search}%")
                  ->orWhere('rfid_uid', 'like', "%{$search}%");
            });
        }

        // Hitung statistik ringkasan
        $stats = [
            'total' => Member::where('school_id', $schoolId)->count(),
            'students' => Member::where('school_id', $schoolId)->where('source_type', 'siswa')->count(),
            'teachers' => Member::where('school_id', $schoolId)->where('source_type', 'guru')->count(),
            'registered_rfid' => Member::where('school_id', $schoolId)->whereNotNull('rfid_uid')->where('rfid_uid', '!=', '')->count(),
        ];

        $members = $query->orderBy('name', 'asc')->paginate(15);

        return view('perpus.member.index', compact('members', 'search', 'type', 'stats'));
    }
}
