<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceSyncService;

class SyncController extends Controller
{
    protected $syncService;

    public function __construct(AttendanceSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Memproses sinkronisasi data dari database absensi.
     */
    public function sync(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        try {
            if ($user->isSuperAdmin()) {
                $schoolId = $request->input('school_id');
                if ($schoolId) {
                    $this->syncService->syncSchool($schoolId);
                } else {
                    $this->syncService->syncAll();
                }
            } else {
                // School Admin / Teacher
                $this->syncService->syncSchool($user->school_id);
            }

            return redirect()->back()->with('success', 'Sinkronisasi data dari sistem absensi berhasil.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }
}
