<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Member;

class VisitController extends Controller
{
    /**
     * Tampilkan riwayat kunjungan perpustakaan.
     */
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $search = $request->input('search');
        $date = $request->input('date', today()->format('Y-m-d'));

        $query = Visit::with('member')
            ->where('school_id', $schoolId)
            ->orderBy('scanned_at', 'desc');

        if ($date) {
            $query->whereDate('scanned_at', $date);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('member', function($m) use ($search) {
                      $m->where('name', 'like', "%{$search}%")
                        ->orWhere('member_code', 'like', "%{$search}%");
                  });
            });
        }

        $visits = $query->paginate(15);
        $members = Member::where('school_id', $schoolId)->orderBy('name', 'asc')->get();

        return view('perpus.kunjungan.index', compact('visits', 'search', 'date', 'members'));
    }

    /**
     * Simpan data kunjungan manual (tamu / siswa tanpa kartu).
     */
    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'visitor_name' => 'required_without:member_id|nullable|string|max:255',
            'class_or_dept' => 'nullable|string|max:100',
            'purpose' => 'nullable|string|max:255',
        ]);

        $data = $request->all();
        $data['school_id'] = $schoolId;
        $data['scanned_at'] = now();

        if ($request->filled('member_id')) {
            $member = Member::findOrFail($request->member_id);
            if ($member->school_id !== $schoolId) {
                abort(403);
            }
            $data['visitor_name'] = null;
            $data['class_or_dept'] = null;
        }

        Visit::create($data);

        return redirect()->route('perpus.kunjungan.index')->with('success', 'Kunjungan manual berhasil dicatat.');
    }
}
