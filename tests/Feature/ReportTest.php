<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Models\Member;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private $school;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'id' => 1,
            'name' => 'SMK Negeri 1 Rekap Test',
            'is_perpus_active' => true,
        ]);

        $this->admin = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Admin Rekap',
            'username' => 'adminrekap',
            'email' => 'adminrekap@test.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);
    }

    public function test_school_admin_can_view_visit_recap_page(): void
    {
        $member = Member::create([
            'school_id' => $this->school->id,
            'source_type' => 'siswa',
            'source_id' => 101,
            'member_code' => '1001',
            'name' => 'Siswa Rekap Test',
            'class_or_dept' => 'XII RPL 1',
        ]);

        Visit::create([
            'school_id' => $this->school->id,
            'member_id' => $member->id,
            'purpose' => 'Belajar kelompok',
            'scanned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('perpus.reports.visits', [
                'kelas' => 'XII',
                'jurusan' => 'RPL',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Rekap Kunjungan Perpustakaan');
        $response->assertSee('Siswa Rekap Test');
    }

    public function test_school_admin_can_view_visit_print_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('perpus.reports.visits.print'));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN REKAPITULASI KUNJUNGAN PERPUSTAKAAN');
    }

    public function test_school_admin_can_view_loan_recap_page(): void
    {
        $member = Member::create([
            'school_id' => $this->school->id,
            'source_type' => 'siswa',
            'source_id' => 102,
            'member_code' => '1002',
            'name' => 'Peminjam Rekap Test',
            'class_or_dept' => 'XI TKJ 2',
        ]);

        $book = Book::create([
            'school_id' => $this->school->id,
            'code' => 'BK-100',
            'title' => 'Buku Pemrograman Laravel',
            'stock' => 10,
            'sisa_stok' => 9,
        ]);

        Loan::create([
            'school_id' => $this->school->id,
            'member_id' => $member->id,
            'book_id' => $book->id,
            'borrow_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'dipinjam',
            'qty' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('perpus.reports.loans', [
                'status' => 'dipinjam',
                'kelas' => 'XI',
                'jurusan' => 'TKJ',
            ]));

        $response->assertStatus(200);
        $response->assertSee('Rekap Peminjaman Buku');
        $response->assertSee('Peminjam Rekap Test');
        $response->assertSee('Buku Pemrograman Laravel');
    }

    public function test_school_admin_can_view_loan_print_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('perpus.reports.loans.print'));

        $response->assertStatus(200);
        $response->assertSee('LAPORAN REKAPITULASI PEMINJAMAN BUKU');
    }

    public function test_teacher_loan_with_student_pickup_creates_visits_for_both_student_and_teacher(): void
    {
        $student = Member::create([
            'school_id' => $this->school->id,
            'source_type' => 'siswa',
            'source_id' => 201,
            'member_code' => 'SISWA01',
            'name' => 'Siswa Pengambil',
            'class_or_dept' => 'X RPL 1',
        ]);

        $teacher = Member::create([
            'school_id' => $this->school->id,
            'source_type' => 'guru',
            'source_id' => 202,
            'member_code' => 'GURU01',
            'name' => 'Guru Peminjam',
            'class_or_dept' => 'Guru / Staf',
        ]);

        $book = Book::create([
            'school_id' => $this->school->id,
            'code' => 'BK-200',
            'title' => 'Buku Panduan Guru',
            'stock' => 5,
            'sisa_stok' => 5,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('perpus.loan.store-manual'), [
                'book_ids' => [$book->id],
                'member_id' => $student->id,
                'borrower_type' => 'guru',
                'teacher_id' => $teacher->id,
                'borrow_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'qty' => 1,
            ]);

        $response->assertStatus(200);

        // Verify visits exist for BOTH student and teacher
        $this->assertDatabaseHas('visits', [
            'school_id' => $this->school->id,
            'member_id' => $student->id,
        ]);

        $this->assertDatabaseHas('visits', [
            'school_id' => $this->school->id,
            'member_id' => $teacher->id,
        ]);
    }
}
