<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    /**
     * Tampilkan daftar device scanner.
     */
    public function index()
    {
        $schoolId = auth()->user()->school_id;
        $devices = Device::where('school_id', $schoolId)->orderBy('created_at', 'desc')->get();
        return view('perpus.device.index', compact('devices'));
    }

    /**
     * Daftarkan device baru dan generate API Key.
     */
    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:rfid_perpus_kunjungan,rfid_perpus_pinjam',
            'active' => 'required|boolean',
        ]);

        Device::create([
            'school_id' => $schoolId,
            'name' => $request->name,
            'api_key' => Str::random(32), // Generate API Key unik
            'type' => $request->type,
            'active' => $request->active,
        ]);

        return redirect()->route('perpus.device.index')->with('success', 'Scanner RFID berhasil didaftarkan. Silakan gunakan API Key yang dihasilkan.');
    }

    /**
     * Perbarui data device.
     */
    public function update(Request $request, $id)
    {
        $device = Device::findOrFail($id);
        $schoolId = auth()->user()->school_id;
        if ($device->school_id !== $schoolId) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:rfid_perpus_kunjungan,rfid_perpus_pinjam',
            'active' => 'required|boolean',
        ]);

        $device->update($request->all());

        return redirect()->route('perpus.device.index')->with('success', 'Scanner RFID berhasil diperbarui.');
    }

    /**
     * Hapus device.
     */
    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $schoolId = auth()->user()->school_id;
        if ($device->school_id !== $schoolId) {
            abort(403);
        }

        $device->delete();

        return redirect()->route('perpus.device.index')->with('success', 'Scanner RFID berhasil dihapus.');
    }

    /**
     * Tampilkan simulator RFID.
     */
    public function simulator()
    {
        $schoolId = auth()->user()->school_id;
        $devices = Device::where('school_id', $schoolId)->where('active', true)->get();
        $members = \App\Models\Member::where('school_id', $schoolId)
            ->whereNotNull('rfid_uid')
            ->where('rfid_uid', '!=', '')
            ->orderBy('name', 'asc')
            ->get();

        return view('perpus.device.simulator', compact('devices', 'members'));
    }
}
