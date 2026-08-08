<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    private $superAdmin;
    private $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'full_name' => 'Super Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@test.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'super_admin',
            'school_id' => null,
        ]);

        $this->school = School::create([
            'id' => 1,
            'name' => 'SMK Negeri 1 Impersonate Test',
            'is_perpus_active' => true,
        ]);
    }

    public function test_super_admin_can_impersonate_school_tenant(): void
    {
        // Pre-create school admin
        $schoolAdmin = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'School Admin',
            'username' => 'schooladmin',
            'email' => 'admin@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.schools.impersonate', $this->school->id));

        $response->assertRedirect(route('perpus.dashboard'));
        $response->assertSessionHas('impersonator_id', $this->superAdmin->id);
        $this->assertEquals($schoolAdmin->id, auth()->id());
    }

    public function test_impersonate_creates_school_admin_if_none_exists(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.schools.impersonate', $this->school->id));

        $response->assertRedirect(route('perpus.dashboard'));
        $this->assertDatabaseHas('users', [
            'school_id' => $this->school->id,
            'role' => 'admin',
        ]);
        $this->assertNotNull(auth()->user());
        $this->assertEquals($this->school->id, auth()->user()->school_id);
    }

    public function test_leaving_impersonation_returns_to_super_admin(): void
    {
        $schoolAdmin = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'School Admin',
            'username' => 'schooladmin',
            'email' => 'admin@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Start impersonating
        $this->actingAs($this->superAdmin)
            ->post(route('superadmin.schools.impersonate', $this->school->id));

        $this->assertEquals($schoolAdmin->id, auth()->id());

        // Leave impersonation
        $response = $this->post(route('superadmin.schools.impersonate.leave'));

        $response->assertRedirect(route('superadmin.schools.index'));
        $this->assertEquals($this->superAdmin->id, auth()->id());
        $this->assertFalse(session()->has('impersonator_id'));
    }

    public function test_non_superadmin_cannot_impersonate(): void
    {
        $regularUser = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Teacher User',
            'username' => 'teacher',
            'email' => 'teacher@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'teacher',
        ]);

        $response = $this->actingAs($regularUser)
            ->post(route('superadmin.schools.impersonate', $this->school->id));

        $response->assertStatus(403);
    }
}
