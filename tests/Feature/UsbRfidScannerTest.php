<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\School;
use App\Models\User;
use App\Models\Visit;
use App\Support\RfidUid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsbRfidScannerTest extends TestCase
{
    use RefreshDatabase;

    private $school;
    private $admin;
    private $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'id' => 1,
            'name' => 'SMK USB RFID Test',
            'is_perpus_active' => true,
        ]);

        $this->admin = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Admin USB',
            'username' => 'adminusb',
            'email' => 'adminusb@test.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        // UID tersimpan dalam format HEX (dari scanner jaringan / sinkronisasi)
        $this->member = Member::create([
            'school_id' => $this->school->id,
            'source_type' => 'siswa',
            'source_id' => 1,
            'member_code' => '2001',
            'name' => 'Siswa USB',
            'class_or_dept' => 'XI TKJ',
            'rfid_uid' => 'A1B2C3D4',
        ]);
    }

    public function test_uid_candidates_convert_between_decimal_and_hex(): void
    {
        $this->assertContains('A1B2C3D4', RfidUid::candidates('2712847316'));     // desimal big-endian
        $this->assertContains('A1B2C3D4', RfidUid::candidates('3569595041'));     // desimal little-endian
        $this->assertContains('2712847316', RfidUid::candidates('a1:b2:c3:d4'));  // hex -> desimal
        $this->assertSame([], RfidUid::candidates('  '));
    }

    public function test_lookup_finds_member_from_decimal_usb_output(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('perpus.rfid.usb.lookup'), ['uid' => '2712847316', 'with_loans' => true])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('member.id', $this->member->id)
            ->assertJsonPath('loans', []);
    }

    public function test_lookup_returns_404_for_unknown_card(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('perpus.rfid.usb.lookup'), ['uid' => '0000000001'])
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_usb_visit_is_recorded_once_within_five_minutes(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('perpus.rfid.usb.visit'), ['uid' => 'A1B2C3D4'])
            ->assertOk()
            ->assertJsonPath('duplicate', false);

        $this->actingAs($this->admin)
            ->postJson(route('perpus.rfid.usb.visit'), ['uid' => '2712847316'])
            ->assertOk()
            ->assertJsonPath('duplicate', true);

        $this->assertSame(1, Visit::where('member_id', $this->member->id)->count());
    }

    public function test_guest_cannot_use_usb_endpoints(): void
    {
        $this->postJson(route('perpus.rfid.usb.lookup'), ['uid' => 'A1B2C3D4'])->assertUnauthorized();
    }
}
