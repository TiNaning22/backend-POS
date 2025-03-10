<?php

namespace App\Http\Controllers;

use App\Models\ProdukToko;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class ProdukTokoController extends Controller
{
    public function index(): JsonResponse
    {
        $produkTokos = ProdukToko::with(['product', 'toko'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all produk toko',
            'data' => $produkTokos
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'toko_id' => 'required|exists:tokos,id',
            'stok' => 'required|integer|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
        ]);

        $produkToko = ProdukToko::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk Toko created successfully',
            'data' => $produkToko
        ], Response::HTTP_CREATED);
    }

    public function show(ProdukToko $produkToko): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Produk Toko details',
            'data' => $produkToko->load(['product', 'toko'])
        ], Response::HTTP_OK);
    }

    public function update(Request $request, ProdukToko $produkToko): JsonResponse
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'toko_id' => 'required|exists:tokos,id',
            'stok' => 'required|integer|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'harga_jual' => 'nullable|numeric|min:0',
        ]);

        $produkToko->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk Toko updated successfully',
            'data' => $produkToko
        ], Response::HTTP_OK);
    }

    public function destroy(ProdukToko $produkToko): JsonResponse
    {
        $produkToko->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Produk Toko deleted successfully'
        ], Response::HTTP_OK);
    }
}
