<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    private $superAdmin;
    private $activeSchool;
    private $inactiveSchool;

    protected function setUp(): void
    {
        parent::setUp();

        // Create super admin
        $this->superAdmin = User::create([
            'email' => 'superadmin@test.com',
            'full_name' => 'Super Admin Test',
            'username' => 'superadmintest',
            'password_hash' => bcrypt('password123'),
            'role' => 'super_admin',
            'school_id' => null,
        ]);

        // Create schools
        $this->activeSchool = School::create([
            'id' => 1,
            'name' => 'Active School',
            'is_perpus_active' => true,
        ]);

        $this->inactiveSchool = School::create([
            'id' => 2,
            'name' => 'Inactive School',
            'is_perpus_active' => false,
        ]);
    }

    /**
     * Test Super Admin dashboard renders successfully.
     */
    public function test_super_admin_dashboard_renders(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('perpus.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Super Admin');
        $response->assertSee('Active School');
        $response->assertSee('Inactive School');
    }

    /**
     * Test toggling school active status.
     */
    public function test_toggling_school_active_status(): void
    {
        $this->assertTrue((bool) $this->activeSchool->is_perpus_active);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.schools.toggle-active', $this->activeSchool->id));

        $response->assertRedirect();
        $this->activeSchool->refresh();
        $this->assertFalse((bool) $this->activeSchool->is_perpus_active);
    }

    /**
     * Test creating a school admin.
     */
    public function test_creating_school_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.admins.store'), [
                'school_id' => $this->activeSchool->id,
                'full_name' => 'New School Admin',
                'username' => 'newadmin',
                'email' => 'newadmin@school.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('superadmin.admins.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'newadmin',
            'role' => 'admin',
            'school_id' => $this->activeSchool->id,
        ]);
    }

    /**
     * Test updating a school admin.
     */
    public function test_updating_school_admin(): void
    {
        $admin = User::create([
            'school_id' => $this->activeSchool->id,
            'full_name' => 'Old Admin',
            'username' => 'oldadmin',
            'email' => 'oldadmin@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('superadmin.admins.update', $admin->id), [
                'school_id' => $this->inactiveSchool->id,
                'full_name' => 'Updated Admin',
                'username' => 'updatedadmin',
                'email' => 'updatedadmin@school.com',
            ]);

        $response->assertRedirect(route('superadmin.admins.index'));
        $admin->refresh();
        $this->assertEquals('Updated Admin', $admin->full_name);
        $this->assertEquals('updatedadmin', $admin->username);
        $this->assertEquals($this->inactiveSchool->id, $admin->school_id);
    }

    /**
     * Test deleting a school admin.
     */
    public function test_deleting_school_admin(): void
    {
        $admin = User::create([
            'school_id' => $this->activeSchool->id,
            'full_name' => 'Delete Admin',
            'username' => 'deladmin',
            'email' => 'deladmin@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('superadmin.admins.destroy', $admin->id));

        $response->assertRedirect(route('superadmin.admins.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $admin->id,
        ]);
    }

    /**
     * Test user from inactive school gets blocked on login/access.
     */
    public function test_user_from_inactive_school_gets_blocked(): void
    {
        $inactiveSchoolAdmin = User::create([
            'school_id' => $this->inactiveSchool->id,
            'full_name' => 'Inactive Admin',
            'username' => 'inactiveadmin',
            'email' => 'inactiveadmin@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // Accessing main route as inactive school admin should redirect to login and log them out
        $response = $this->actingAs($inactiveSchoolAdmin)
            ->get(route('perpus.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login']);
        $this->assertFalse(auth()->check());
    }
}
