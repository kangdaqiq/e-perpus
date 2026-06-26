<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Models\Member;
use App\Models\Book;
use App\Models\Visit;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPointsFinesTest extends TestCase
{
    use RefreshDatabase;

    private $school;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a default school
        $this->school = School::create([
            'id' => 1,
            'name' => 'Test School',
            'point_borrow' => 10,
            'point_visit' => 5,
            'fine_per_day' => 1000.00,
            'is_perpus_active' => true
        ]);

        // Create an admin user associated with the school
        $this->admin = User::create([
            'email' => 'admin@test.com',
            'full_name' => 'Admin Test',
            'username' => 'admintest',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
            'school_id' => $this->school->id,
        ]);
    }

    /**
     * Test navigating to settings page and rendering the form.
     */
    public function test_settings_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('perpus.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Perpustakaan');
        $response->assertSee('point_borrow');
        $response->assertSee('point_visit');
        $response->assertSee('fine_per_day');
        $response->assertSee('10');
        $response->assertSee('5');
        $response->assertSee('1000');
    }

    /**
     * Test updating configuration settings.
     */
    public function test_updating_settings_updates_database(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('perpus.settings.update'), [
                'point_borrow' => 15,
                'point_visit' => 8,
                'fine_per_day' => 2000.00
            ]);

        $response->assertRedirect(route('perpus.settings.index'));
        $response->assertSessionHas('success', 'Pengaturan berhasil diperbarui.');

        // Refresh school from DB and verify values
        $this->school->refresh();
        $this->assertEquals(15, $this->school->point_borrow);
        $this->assertEquals(8, $this->school->point_visit);
        $this->assertEquals(2000.00, $this->school->fine_per_day);
    }

    /**
     * Test that dynamic settings are used for dashboard leaderboard calculations.
     */
    public function test_leaderboard_uses_configured_points(): void
    {
        // Set specific points
        $this->school->update([
            'point_borrow' => 15,
            'point_visit' => 8
        ]);

        // Create 2 members
        $member1 = Member::create([
            'school_id' => $this->school->id,
            'source_type' => 'siswa',
            'source_id' => 101,
            'member_code' => 'M001',
            'name' => 'Student One',
            'class_or_dept' => 'XI-A'
        ]);

        $member2 = Member::create([
            'school_id' => $this->school->id,
            'source_type' => 'siswa',
            'source_id' => 102,
            'member_code' => 'M002',
            'name' => 'Student Two',
            'class_or_dept' => 'XI-B'
        ]);

        $book = Book::create([
            'school_id' => $this->school->id,
            'code' => 'B001',
            'title' => 'Sample Book',
            'stock' => 10,
            'sisa_stok' => 10
        ]);

        // Member 1: 1 loan, 2 visits
        Loan::create([
            'school_id' => $this->school->id,
            'member_id' => $member1->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::now()->subDays(2),
            'due_date' => Carbon::now()->addDays(5),
            'status' => 'dipinjam',
            'qty' => 1
        ]);
        Visit::create([
            'school_id' => $this->school->id,
            'member_id' => $member1->id,
            'purpose' => 'Read',
            'scanned_at' => Carbon::now()->subDays(2)
        ]);
        Visit::create([
            'school_id' => $this->school->id,
            'member_id' => $member1->id,
            'purpose' => 'Read',
            'scanned_at' => Carbon::now()->subDays(1)
        ]);
        // Member 1 Points expected: (1 loan * 15) + (2 visits * 8) = 15 + 16 = 31 points.

        // Member 2: 2 loans, 1 visit
        Loan::create([
            'school_id' => $this->school->id,
            'member_id' => $member2->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::now()->subDays(3),
            'due_date' => Carbon::now()->addDays(4),
            'status' => 'dipinjam',
            'qty' => 1
        ]);
        Loan::create([
            'school_id' => $this->school->id,
            'member_id' => $member2->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::now()->subDays(2),
            'due_date' => Carbon::now()->addDays(5),
            'status' => 'dipinjam',
            'qty' => 1
        ]);
        Visit::create([
            'school_id' => $this->school->id,
            'member_id' => $member2->id,
            'purpose' => 'Borrow',
            'scanned_at' => Carbon::now()->subDays(2)
        ]);
        // Member 2 Points expected: (2 loans * 15) + (1 visit * 8) = 30 + 8 = 38 points.

        $response = $this->actingAs($this->admin)
            ->get(route('perpus.dashboard'));

        $response->assertStatus(200);
        
        // Assert that both members are present on the leaderboard with their respective calculated points.
        // And that student two (38 points) is displayed first, followed by student one (31 points).
        $response->assertSee('Student Two');
        $response->assertSee('Student One');

        // Let's assert the dynamic calculated points in the view context or content if passed
        $topMembers = $response->viewData('topMembers');
        $this->assertCount(2, $topMembers);
        
        // Verify Member 2 is first and has 38 points
        $this->assertEquals($member2->id, $topMembers->first()->id);
        $this->assertEquals(38, $topMembers->first()->points);

        // Verify Member 1 is second and has 31 points
        $this->assertEquals($member1->id, $topMembers->last()->id);
        $this->assertEquals(31, $topMembers->last()->points);
    }

    /**
     * Test returning a book calculates the correct fine using custom configuration.
     */
    public function test_return_book_calculates_correct_fine_using_custom_fine_rate(): void
    {
        // Set custom fine rate of 2500 per day
        $this->school->update([
            'fine_per_day' => 2500.00
        ]);

        $member = Member::create([
            'school_id' => $this->school->id,
            'source_type' => 'siswa',
            'source_id' => 103,
            'member_code' => 'M003',
            'name' => 'Late Student',
            'class_or_dept' => 'XII'
        ]);

        $book = Book::create([
            'school_id' => $this->school->id,
            'code' => 'B002',
            'title' => 'Late Book',
            'stock' => 10,
            'sisa_stok' => 9 // sisa stok after 1 copy borrowed
        ]);

        // Borrow date: 10 days ago, Due date: 3 days ago.
        // Today is return date, which is 3 days late.
        $dueDate = Carbon::now()->subDays(3);
        $loan = Loan::create([
            'school_id' => $this->school->id,
            'member_id' => $member->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::now()->subDays(10),
            'due_date' => $dueDate,
            'status' => 'dipinjam',
            'qty' => 1
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('perpus.loan.return', ['id' => $loan->id]), [
                'return_qty' => 1,
                'return_date' => Carbon::now()->format('Y-m-d')
            ]);

        $response->assertRedirect(route('perpus.loan.index'));
        
        // Assert successful return with fine calculated
        // Fine expected = 3 days late * 2500 fine_per_day = 7500.00
        $loan->refresh();
        $this->assertEquals('kembali', $loan->status);
        $this->assertEquals(7500.00, $loan->fine);
        $this->assertNotNull($loan->return_date);

        // Verify book stock is restored
        $book->refresh();
        $this->assertEquals(10, $book->sisa_stok);
    }
}
