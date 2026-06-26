<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Membuat satu sekolah dan satu akun admin default.
     */
    public function run(): void
    {
        // Buat sekolah default (jika belum ada)
        $school = School::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Sekolah Default',
                'is_perpus_active' => true
            ]
        );
        if (!$school->is_perpus_active) {
            $school->update(['is_perpus_active' => true]);
        }

        // Buat akun super admin default
        User::firstOrCreate(
            ['email' => 'superadmin@eperpus.com'],
            [
                'full_name'     => 'Super Administrator',
                'username'      => 'superadmin',
                'password_hash' => Hash::make('superpassword'),
                'role'          => 'super_admin',
                'school_id'     => null,
            ]
        );

        // Buat akun admin default
        User::firstOrCreate(
            ['email' => 'admin@eperpus.com'],
            [
                'full_name'     => 'Administrator',
                'username'      => 'admin',
                'password_hash' => Hash::make('password123'),
                'role'          => 'admin',
                'school_id'     => $school->id,
            ]
        );
    }
}
