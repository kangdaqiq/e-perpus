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
}
