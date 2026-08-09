<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookOcrTest extends TestCase
{
    use RefreshDatabase;

    private $school;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
            'id' => 1,
            'name' => 'SMK Test OCR',
            'is_perpus_active' => true,
        ]);

        $this->admin = User::create([
            'school_id' => $this->school->id,
            'full_name' => 'Admin OCR',
            'username' => 'adminocr',
            'email' => 'adminocr@test.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
        ]);
    }

    public function test_ocr_requires_authentication(): void
    {
        $response = $this->postJson(route('perpus.buku.scan-ocr'), [
            'image' => UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg'),
        ]);

        $response->assertStatus(401);
    }

    public function test_ocr_validates_image_input(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('perpus.buku.scan-ocr'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    public function test_ocr_returns_error_when_api_key_is_missing(): void
    {
        Config::set('services.gemini.api_key', null);
        putenv('GEMINI_API_KEY=');

        $response = $this->actingAs($this->admin)->postJson(route('perpus.buku.scan-ocr'), [
            'image' => UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg'),
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('API Key Gemini', $response->json('message'));
    }

    public function test_ocr_successfully_parses_gemini_response(): void
    {
        Config::set('services.gemini.api_key', 'fake-gemini-key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'code' => '978-602-03-8888-8',
                                        'title' => 'Laskar Pelangi',
                                        'author' => 'Andrea Hirata',
                                        'publisher' => 'Bentang Pustaka',
                                        'year' => 2005,
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('perpus.buku.scan-ocr'), [
            'image' => UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg'),
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'code' => '978-602-03-8888-8',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'year' => 2005,
            ]
        ]);
    }
}
