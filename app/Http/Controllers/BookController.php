<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Tampilkan katalog buku dengan pencarian.
     */
    public function index(Request $request)
    {
        // Auto-fix enum status column if 'completed' is not supported in the database enum
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE pending_verifications MODIFY COLUMN status ENUM('pending', 'verified', 'failed', 'expired', 'completed') DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Silence
        }

        $schoolId = auth()->user()->school_id;
        $search = $request->input('search');

        $query = Book::where('school_id', $schoolId)->orderBy('title', 'asc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $books = $query->paginate(10);
        $devices = \App\Models\Device::where('school_id', $schoolId)
            ->where('type', 'rfid_perpus_pinjam')
            ->where('active', true)
            ->get();
        $members = \App\Models\Member::where('school_id', $schoolId)->orderBy('name', 'asc')->get();
        return view('perpus.buku.index', compact('books', 'search', 'devices', 'members'));
    }

    /**
     * Simpan buku baru.
     */
    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'code' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'stock' => 'required|integer|min:0',
            'location' => 'nullable|string|max:100',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validasi kode buku unik per sekolah
        $exists = Book::where('school_id', $schoolId)->where('code', $request->code)->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['code' => 'Kode Buku / ISBN sudah terdaftar.'])->withInput();
        }

        $data = $request->except('cover');
        $data['school_id'] = $schoolId;
        $data['sisa_stok'] = $request->stock;

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('covers', 'public');
            $data['cover_url'] = '/storage/' . $path;
        }

        Book::create($data);

        return redirect()->route('perpus.buku.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    /**
     * Update data buku.
     */
    public function update(Request $request, $id)
    {
        $buku = Book::findOrFail($id);
        $schoolId = auth()->user()->school_id;
        if ($buku->school_id !== $schoolId) {
            abort(403);
        }

        $request->validate([
            'code' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'stock' => 'required|integer|min:0',
            'location' => 'nullable|string|max:100',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $exists = Book::where('school_id', $schoolId)
            ->where('code', $request->code)
            ->where('id', '!=', $buku->id)
            ->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['code' => 'Kode Buku / ISBN sudah terdaftar.'])->withInput();
        }

        $data = $request->except('cover');

        // Sesuaikan sisa_stok
        $diff = $request->stock - $buku->stock;
        $newSisa = $buku->sisa_stok + $diff;
        if ($newSisa < 0) {
            return redirect()->back()->withErrors(['stock' => 'Stok tidak dapat diturunkan di bawah jumlah buku terpinjam.'])->withInput();
        }
        $data['sisa_stok'] = $newSisa;

        if ($request->hasFile('cover')) {
            if ($buku->cover_url && !str_starts_with($buku->cover_url, 'http')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $buku->cover_url));
            }
            $path = $request->file('cover')->store('covers', 'public');
            $data['cover_url'] = '/storage/' . $path;
        }

        $buku->update($data);

        return redirect()->route('perpus.buku.index')->with('success', 'Buku berhasil diperbarui.');
    }

    /**
     * Hapus buku.
     */
    public function destroy($id)
    {
        $buku = Book::findOrFail($id);
        $schoolId = auth()->user()->school_id;
        if ($buku->school_id !== $schoolId) {
            abort(403);
        }

        if ($buku->cover_url && !str_starts_with($buku->cover_url, 'http')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $buku->cover_url));
        }

        $buku->delete();

        return redirect()->route('perpus.buku.index')->with('success', 'Buku berhasil dihapus.');
    }

    /**
     * Pindai sampul / halaman buku menggunakan AI Gemini Vision (OCR)
     */
    public function scanOcr(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API Key Gemini (GEMINI_API_KEY) belum diatur di file .env. Silakan atur GEMINI_API_KEY terlebih dahulu.',
            ], 422);
        }

        try {
            $file = $request->file('image');
            $mimeType = $file->getMimeType();
            $base64Image = base64_encode(file_get_contents($file->getRealPath()));

            $prompt = 'Analisis foto buku ini (sampul/cover atau halaman hak cipta). Ekstrak informasi berikut dalam format JSON murni tanpa markdown/penjelasan tambahan.
Schema JSON yang wajib dipenuhi:
{
  "code": "string/null (ISBN atau Kode Buku jika terlihat)",
  "title": "string/null (Judul Utama Buku)",
  "author": "string/null (Nama Penulis/Pengarang)",
  "publisher": "string/null (Nama Penerbit)",
  "year": integer/null (Tahun Terbit 4 digit, contoh: 2023)
}
Jika salah satu bidang tidak ditemukan/kurang jelas, berikan nilai null.';

            $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash');
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            
            $response = \Illuminate\Support\Facades\Http::timeout(30)->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image,
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                // Fallback attempt with gemini-1.5-flash
                $fallbackEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
                $response = \Illuminate\Support\Facades\Http::timeout(30)->post($fallbackEndpoint, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $base64Image,
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json',
                    ],
                ]);
            }

            if ($response->failed()) {
                $errorMessage = $response->json('error.message') ?? 'Gagal memproses gambar dengan Gemini AI.';
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 500);
            }

            $jsonText = $response->json('candidates.0.content.parts.0.text');
            $data = json_decode($jsonText, true);

            if (!is_array($data)) {
                $cleanText = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($jsonText ?? ''));
                $data = json_decode($cleanText, true);
            }

            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membaca format JSON dari respons AI.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'code' => $data['code'] ?? null,
                    'title' => $data['title'] ?? null,
                    'author' => $data['author'] ?? null,
                    'publisher' => $data['publisher'] ?? null,
                    'year' => isset($data['year']) ? (int) $data['year'] : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ], 500);
        }
    }
}
