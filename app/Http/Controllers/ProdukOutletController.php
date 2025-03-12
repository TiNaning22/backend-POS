<?php

namespace App\Http\Controllers;

use App\Models\ProdukOutlet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class ProdukOutletController extends Controller
{
    public function index(): JsonResponse
    {
        $produkOutlets = ProdukOutlet::with(['product', 'outlet'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all produk outlet',
            'data' => $produkOutlets
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'outlet_id' => 'required|exists:outlets,id',
            'stok' => 'required|integer|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            // 'harga_jual' => 'nullable|numeric|min:0',
        ]);

        $produkOutlet = ProdukOutlet::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk outlet created successfully',
            'data' => $produkOutlet
        ], Response::HTTP_CREATED);
    }

    public function show($id): JsonResponse
    {

        $produkOutlet = ProdukOutlet::find($id);

        if (!$produkOutlet) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk Outlet tidak ditemukan',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Produk Outlet details',
            'data' => $produkOutlet->load(['product', 'outlet'])
        ], Response::HTTP_OK);
    }

    public function update(Request $request, ProdukOutlet $produkOutlet): JsonResponse
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'outlet_id' => 'required|exists:outlets,id',
            'stok' => 'required|integer|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            // 'harga_jual' => 'nullable|numeric|min:0',
        ]);

        $produkOutlet->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk Outlet updated successfully',
            'data' => $produkOutlet
        ], Response::HTTP_OK);
    }

    public function destroy(ProdukOutlet $produkOutlet): JsonResponse
    {
        $produkOutlet->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk Toko deleted successfully'
        ], Response::HTTP_OK);
    }
}
