<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Storage;


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
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'barcode' => 'nullable|string',
            'kategori_id' => 'nullable|exists:categories,id',
            'toko_id' => 'nullable|exists:tokos,id',
        ]);

        // Handle image upload
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products', $imageName, 'public');
            $validatedData['gambar'] = $path;
        }

        $product = Products::create($validatedData);
        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product
        ], Response::HTTP_CREATED);
    }

    public function show(Products $product)
    {
                // Add full URL for image
        if ($product->gambar) {
            $product->gambar = asset('storage/' . $product->gambar);
        }
        return response()->json($product, Response::HTTP_OK);
    }

    public function update(Request $request, Products $product)
    {
        try{

        
        $validatedData = $request->validate([
            'kode_produk' => 'sometimes|required|string|max:255',
            'nama_produk' => 'sometimes|required|string|max:255',
            'harga' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Changed to accept image files
            'barcode' => 'nullable|string',
            'kategori_id' => 'nullable|exists:categories,id',
            'toko_id' => 'nullable|exists:tokos,id',
        ]);

        // Handle image upload for update
        if ($request->hasFile('gambar')) {
            // Delete old image if exists
            if ($product->gambar && Storage::disk('public')->exists($product->gambar)) {
                Storage::disk('public')->delete($product->gambar);
            }
            
            $image = $request->file('gambar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products', $imageName, 'public');
            $validatedData['gambar'] = $path;
        }

        $product->update($validatedData);
        
        // Add full URL for image in response
        if ($product->gambar) {
            $product->gambar = asset('storage/' . $product->gambar);
        }
        
        return response()->json($validatedData, Response::HTTP_OK);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }

    public function destroy(Products $product)
    {
        // Delete image file if exists
        if ($product->gambar && Storage::disk('public')->exists($product->gambar)) {
            Storage::disk('public')->delete($product->gambar);
        }
        
        $product->delete();
        return response()->json(['message' => 'Products deleted'], Response::HTTP_OK);
    }
}
