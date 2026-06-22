<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\Member;
use App\Models\User;

class AttendanceSyncService
{
    /**
     * Sinkronisasikan seluruh data untuk semua sekolah.
     */
    public function syncAll()
    {
        $this->syncSchools();
        $this->syncUsers();
        $this->syncMembers();
    }

    /**
     * Sinkronisasikan data untuk satu sekolah tertentu.
     */
    public function syncSchool($schoolId)
    {
        $this->syncSchools($schoolId);
        $this->syncUsers($schoolId);
        $this->syncMembers($schoolId);
    }

    /**
     * Sinkronisasi data Sekolah.
     */
    public function syncSchools($schoolId = null)
    {
        $query = DB::connection('mysql_absensi')->table('schools');
        if ($schoolId) {
            $query->where('id', $schoolId);
        }
        $schools = $query->get();

        foreach ($schools as $school) {
            School::updateOrCreate(
                ['id' => $school->id],
                ['name' => $school->name]
            );
        }
    }

    /**
     * Sinkronisasi data Pengguna (Users) - admin/guru/super_admin.
     */
    public function syncUsers($schoolId = null)
    {
        $query = DB::connection('mysql_absensi')->table('users')
            ->whereIn('role', ['admin', 'teacher', 'super_admin']);
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        $users = $query->get();

        foreach ($users as $user) {
            // Pastikan sekolah sudah tersinkron untuk menghindari error Foreign Key
            if ($user->school_id && !School::where('id', $user->school_id)->exists()) {
                $this->syncSchools($user->school_id);
            }

            User::updateOrCreate(
                ['email' => $user->email],
                [
                    'school_id' => $user->school_id,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'password_hash' => $user->password_hash,
                    'role' => $user->role,
                ]
            );
        }
    }

    /**
     * Sinkronisasi data Siswa dan Guru menjadi Member E-Perpus.
     */
    public function syncMembers($schoolId = null)
    {
        // 1. Sinkronisasi Siswa
        $siswaQuery = DB::connection('mysql_absensi')->table('siswa')
            ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->select('siswa.*', 'kelas.nama_kelas');
        if ($schoolId) {
            $siswaQuery->where('siswa.school_id', $schoolId);
        }
        $students = $siswaQuery->get();

        foreach ($students as $student) {
            if ($student->school_id && !School::where('id', $student->school_id)->exists()) {
                $this->syncSchools($student->school_id);
            }

            Member::updateOrCreate(
                [
                    'school_id' => $student->school_id,
                    'source_type' => 'siswa',
                    'source_id' => $student->id
                ],
                [
                    'member_code' => $student->nis ?? ('S-' . $student->id),
                    'name' => $student->nama,
                    'class_or_dept' => $student->nama_kelas ?? 'Tanpa Kelas',
                    'rfid_uid' => $student->uid_rfid ? strtoupper(trim($student->uid_rfid)) : null,
                ]
            );
        }

        // 2. Sinkronisasi Guru/Staf
        $guruQuery = DB::connection('mysql_absensi')->table('guru');
        if ($schoolId) {
            $guruQuery->where('school_id', $schoolId);
        }
        $teachers = $guruQuery->get();

        foreach ($teachers as $teacher) {
            if ($teacher->school_id && !School::where('id', $teacher->school_id)->exists()) {
                $this->syncSchools($teacher->school_id);
            }

            Member::updateOrCreate(
                [
                    'school_id' => $teacher->school_id,
                    'source_type' => 'guru',
                    'source_id' => $teacher->id
                ],
                [
                    'member_code' => $teacher->nip ?? ('G-' . $teacher->id),
                    'name' => $teacher->nama,
                    'class_or_dept' => 'Guru / Staf',
                    'rfid_uid' => $teacher->uid_rfid ? strtoupper(trim($teacher->uid_rfid)) : null,
                ]
            );
        }
    }
}
