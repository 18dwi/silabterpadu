<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    /**
     * Store a new room (Laboran only).
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_ruangan'  => 'required|string|max:30|unique:rooms,kode_ruangan',
            'nama_ruangan'  => 'required|string|max:100',
            'kapasitas'     => 'required|integer|min:1',
            'lokasi'        => 'nullable|string|max:150',
            'deskripsi'     => 'nullable|string',
            'status'        => 'required|in:tersedia,nonaktif',
        ]);

        $user = Auth::user();

        Room::create([
            'kode_ruangan' => strtoupper($request->kode_ruangan),
            'nama_ruangan' => $request->nama_ruangan,
            'kapasitas'    => $request->kapasitas,
            'lokasi'       => $request->lokasi,
            'deskripsi'    => $request->deskripsi,
            'jurusan'      => $user->jurusan,
            'status'       => $request->status,
        ]);

        return redirect()->back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    /**
     * Update an existing room (Laboran only).
     */
    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'kode_ruangan'  => 'required|string|max:30|unique:rooms,kode_ruangan,' . $id,
            'nama_ruangan'  => 'required|string|max:100',
            'kapasitas'     => 'required|integer|min:1',
            'lokasi'        => 'nullable|string|max:150',
            'deskripsi'     => 'nullable|string',
            'status'        => 'required|in:tersedia,nonaktif',
        ]);

        $room->update([
            'kode_ruangan' => strtoupper($request->kode_ruangan),
            'nama_ruangan' => $request->nama_ruangan,
            'kapasitas'    => $request->kapasitas,
            'lokasi'       => $request->lokasi,
            'deskripsi'    => $request->deskripsi,
            'status'       => $request->status,
        ]);

        return redirect()->back()->with('success', 'Data ruangan berhasil diperbarui.');
    }

    /**
     * Delete a room (Laboran only).
     */
    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return redirect()->back()->with('success', 'Ruangan berhasil dihapus.');
    }
}
