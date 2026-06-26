<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;

class SettingController extends Controller
{
    /**
     * Tampilkan Halaman Pengaturan.
     */
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        
        // Auto-fix schools table if settings columns are missing (since CLI php is not working)
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('schools', 'point_borrow')) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE schools ADD COLUMN point_borrow INT DEFAULT 10 AFTER name, ADD COLUMN point_visit INT DEFAULT 5 AFTER point_borrow, ADD COLUMN fine_per_day DECIMAL(10, 2) DEFAULT 1000.00 AFTER point_visit");
            }
        } catch (\Exception $e) {
            // Silence
        }

        $school = School::findOrFail($schoolId);
        return view('perpus.settings.index', compact('school'));
    }

    /**
     * Perbarui Pengaturan.
     */
    public function update(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);

        $request->validate([
            'point_borrow' => 'required|integer|min:0',
            'point_visit' => 'required|integer|min:0',
            'fine_per_day' => 'required|numeric|min:0',
        ]);

        $school->update([
            'point_borrow' => $request->point_borrow,
            'point_visit' => $request->point_visit,
            'fine_per_day' => $request->fine_per_day,
        ]);

        return redirect()->route('perpus.settings.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
