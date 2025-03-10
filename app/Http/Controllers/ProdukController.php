<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Models\Products;
use Illuminate\Http\Response;


class ProdukController extends Controller
{
    public function index()
    {
        $products = Products::all();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all products',
            'data' => $products
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kode_produk' => 'required|string|max:255',
            'nama_produk' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'gambar' => 'nullable|string',
            'barcode' => 'nullable|string',
            'kategori_id' => 'nullable|exists:categories,id',
            'toko_id' => 'nullable|exists:tokos,id',
        ]);

        $product = Products::create($validatedData);
        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product
        ], Response::HTTP_CREATED);
    }

    public function show(Products $product)
    {
        return response()->json($product, Response::HTTP_OK);
    }

    public function update(Request $request, Products $product)
    {
        $validatedData = $request->validate([
            'kode_produk' => 'sometimes|required|string|max:255',
            'nama_produk' => 'sometimes|required|string|max:255',
            'harga' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'gambar' => 'nullable|string',
            'barcode' => 'nullable|string',
            'kategori_id' => 'nullable|exists:categories,id',
            'toko_id' => 'nullable|exists:tokos,id',
        ]);

        $product->update($validatedData);
        return response()->json($product, Response::HTTP_OK);
    }

    public function destroy(Products $product)
    {
        $product->delete();
        return response()->json(['message' => 'Products deleted'], Response::HTTP_OK);
    }
}
