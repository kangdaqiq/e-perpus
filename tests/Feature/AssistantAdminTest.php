<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantAdminTest extends TestCase
{
    use RefreshDatabase;

    private $school;
    private $schoolAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'id' => 1,
            'name' => 'SMK Negeri 1 Test',
            'is_perpus_active' => true,
        ]);

        $this->schoolAdmin = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Main Admin',
            'username' => 'mainadmin',
            'email' => 'admin@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);
    }

    public function test_school_admin_can_view_assistant_admins_page(): void
    {
        $response = $this->actingAs($this->schoolAdmin)
            ->get(route('perpus.assistant-admins.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Admin Pembantu');
    }

    public function test_school_admin_can_create_up_to_two_assistant_admins(): void
    {
        // 1st Assistant Admin
        $response1 = $this->actingAs($this->schoolAdmin)
            ->post(route('perpus.assistant-admins.store'), [
                'full_name' => 'Assistant One',
                'username' => 'assistant1',
                'email' => 'assistant1@school.com',
                'password' => 'password123',
            ]);

        $response1->assertRedirect(route('perpus.assistant-admins.index'));
        $response1->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'username' => 'assistant1',
            'role' => 'admin_pembantu',
            'school_id' => $this->school->id,
        ]);

        // 2nd Assistant Admin
        $response2 = $this->actingAs($this->schoolAdmin)
            ->post(route('perpus.assistant-admins.store'), [
                'full_name' => 'Assistant Two',
                'username' => 'assistant2',
                'email' => 'assistant2@school.com',
                'password' => 'password123',
            ]);

        $response2->assertRedirect(route('perpus.assistant-admins.index'));
        $response2->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'username' => 'assistant2',
            'role' => 'admin_pembantu',
            'school_id' => $this->school->id,
        ]);

        $this->assertEquals(2, User::where('school_id', $this->school->id)->where('role', 'admin_pembantu')->count());
    }

    public function test_school_admin_cannot_create_more_than_two_assistant_admins(): void
    {
        // Create 2 existing assistant admins
        User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Assistant 1',
            'username' => 'ast1',
            'email' => 'ast1@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin_pembantu',
        ]);
        User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Assistant 2',
            'username' => 'ast2',
            'email' => 'ast2@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin_pembantu',
        ]);

        // Attempt to create 3rd assistant admin
        $response = $this->actingAs($this->schoolAdmin)
            ->post(route('perpus.assistant-admins.store'), [
                'full_name' => 'Assistant 3',
                'username' => 'ast3',
                'email' => 'ast3@school.com',
                'password' => 'password123',
            ]);

        $response->assertSessionHas('error', 'Jumlah akun Admin Pembantu telah mencapai batas maksimal (2 akun).');
        $this->assertEquals(2, User::where('school_id', $this->school->id)->where('role', 'admin_pembantu')->count());
    }

    public function test_school_admin_can_update_assistant_admin(): void
    {
        $assistant = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Old Name',
            'username' => 'olduser',
            'email' => 'old@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin_pembantu',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->put(route('perpus.assistant-admins.update', $assistant->id), [
                'full_name' => 'New Updated Name',
                'username' => 'newuser',
                'email' => 'new@school.com',
            ]);

        $response->assertRedirect(route('perpus.assistant-admins.index'));
        $this->assertDatabaseHas('users', [
            'id' => $assistant->id,
            'full_name' => 'New Updated Name',
            'username' => 'newuser',
            'email' => 'new@school.com',
        ]);
    }

    public function test_school_admin_can_delete_assistant_admin(): void
    {
        $assistant = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'To Delete',
            'username' => 'todelete',
            'email' => 'delete@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin_pembantu',
        ]);

        $response = $this->actingAs($this->schoolAdmin)
            ->delete(route('perpus.assistant-admins.destroy', $assistant->id));

        $response->assertRedirect(route('perpus.assistant-admins.index'));
        $this->assertDatabaseMissing('users', [
            'id' => $assistant->id,
        ]);
    }

    public function test_assistant_admin_cannot_manage_assistant_admins(): void
    {
        $assistant = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Assistant User',
            'username' => 'assistantuser',
            'email' => 'assistant@school.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin_pembantu',
        ]);

        $response = $this->actingAs($assistant)
            ->get(route('perpus.assistant-admins.index'));

        $response->assertStatus(403);
    }
}
