<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Inventory;
use Illuminate\Http\Request;
// use Illuminate\Validation\Validator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::with('product')->get();

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'tanggal' => 'required|date',
            'stok_awal' => 'required|integer|min:0',
            'stok_masuk' => 'required|integer|min:0',
            'stok_keluar' => 'required|integer|min:0',
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Menghitung stok akhir
        $stok_akhir = $request->stok_awal + $request->stok_masuk - $request->stok_keluar;

        // Memastikan stok akhir tidak negatif
        if ($stok_akhir < 0) {
            return response()->json([
                'success' => false,
                'message' => 'Stok akhir tidak boleh negatif'
            ], 422);
        }

        // Membuat record inventori baru
        $inventory = Inventory::create([
            'product_id' => $request->product_id,
            'tanggal' => $request->tanggal,
            'stok_awal' => $request->stok_awal,
            'stok_masuk' => $request->stok_masuk,
            'stok_keluar' => $request->stok_keluar,
            'stok_akhir' => $stok_akhir,
            'keterangan' => $request->keterangan
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventori berhasil ditambahkan',
            'data' => $inventory
        ], 201);
    }

    public function destroy($id)
    {
        $inventory = Inventory::find($id);

        if (!$inventory) {
            return response()->json([
                'success' => false,
                'message' => 'Inventori tidak ditemukan'
            ], 404);
        }

        $inventory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inventori berhasil dihapus'
        ]);
    }

    public function getByProduct($productId)
    {
        $product = Products::find($productId);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
        
        $inventories = Inventory::where('product_id', $productId)
                                ->orderBy('tanggal', 'desc')
                                ->get();
                                
        return response()->json([
            'success' => true,
            'message' => 'Data inventori produk berhasil diambil',
            'product' => $product->nama_produk,
            'data' => $inventories
        ]);
    }

    public function getByDateRange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $inventories = Inventory::with('product')
                                ->whereBetween('tanggal', [$request->start_date, $request->end_date])
                                ->orderBy('tanggal', 'asc')
                                ->get();
                                
        return response()->json([
            'success' => true,
            'message' => 'Data inventori berhasil diambil',
            'periode' => $request->start_date . ' hingga ' . $request->end_date,
            'data' => $inventories
        ]);
    }
}
