<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
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
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'barcode' => 'nullable|string',
            'kategori_id' => 'nullable|exists:categories,id',
            // 'toko_id' => 'nullable|exists:tokos,id',
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

    public function show($id)
    {
        $product = Products::find($id);
    
        if (!$product) {
            return response()->json([
                'error' => 'Data produk tidak ditemukan',
                'message' => 'Data produk tidak ditemukan'
            ], 404);
        }
        
        // Add full URL for image
        if ($product->gambar) {
            $product->gambar = asset('storage/' . $product->gambar);
        }
        
        return response()->json(['data' => $product]);
    }
    public function update(Request $request, Products $product)
    {

    try {
        // Force request method to POST when handling files
        if ($request->isMethod('PUT') && $request->hasFile('gambar')) {
            $request->setMethod('POST');
        }

        // Validate request data
        $validatedData = $request->validate([
            'kode_produk' => 'sometimes|required|string|max:255',
            'nama_produk' => 'sometimes|required|string|max:255',
            'harga' => 'sometimes|required|numeric|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'barcode' => 'nullable|string',
            'kategori_id' => 'nullable|exists:categories,id',
        ]);

        // Handle file upload if exists
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            
            // Verify if file is valid
            if ($file->isValid()) {
                // Delete old image if exists
                if ($product->gambar && Storage::disk('public')->exists($product->gambar)) {
                    Storage::disk('public')->delete($product->gambar);
                }

                // Store new image
                $imageName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('products', $imageName, 'public');
                $validatedData['gambar'] = $path;
            } else {
                Log::error('Invalid file upload');
                throw new \Exception('Invalid file upload');
            }
        }

        // Update product
        $product->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Produk berhasil diupdate',
            'data' => $product
        ], Response::HTTP_OK);

    } catch (\Exception $e) {
        Log::error('Error updating product: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'Gagal mengupdate produk',
            'error' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
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
