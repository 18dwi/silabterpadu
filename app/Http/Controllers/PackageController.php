<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    /**
     * Store a newly created package with its items.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $package = Package::create([
                    'nama_paket' => $request->nama_paket,
                    'deskripsi' => $request->deskripsi,
                    'jurusan' => auth()->user()->jurusan,
                ]);

                foreach ($request->items as $itemData) {
                    PackageItem::create([
                        'package_id' => $package->id,
                        'item_id' => $itemData['item_id'],
                        'jumlah' => $itemData['jumlah'],
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Paket alat/bahan berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat paket: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing package with its items.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            $package = Package::findOrFail($id);

            DB::transaction(function () use ($package, $request) {
                $package->update([
                    'nama_paket' => $request->nama_paket,
                    'deskripsi' => $request->deskripsi,
                ]);

                // Sync items: delete old ones and create new ones
                $package->items()->delete();

                foreach ($request->items as $itemData) {
                    PackageItem::create([
                        'package_id' => $package->id,
                        'item_id' => $itemData['item_id'],
                        'jumlah' => $itemData['jumlah'],
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Paket berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui paket: ' . $e->getMessage());
        }
    }

    /**
     * Delete a package.
     */
    public function destroy($id)
    {
        try {
            $package = Package::findOrFail($id);
            $package->delete();

            return redirect()->back()->with('success', 'Paket berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint to get details of a specific package for dynamic client insertion.
     */
    public function showApi($id)
    {
        $package = Package::with('items.item')->findOrFail($id);
        return response()->json($package);
    }
}
