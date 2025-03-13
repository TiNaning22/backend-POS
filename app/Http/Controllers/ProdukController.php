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

            Log::info('Raw request content:', [
                'content' => $request->getContent(),
                'headers' => $request->headers->all(),
                'method' => $request->method(),
                'has_file' => $request->hasFile('gambar'),
                'all_files' => $request->allFiles()
            ]);
            // Validasi request data
            $validatedData = $request->validate([
                'kode_produk' => 'sometimes|required|string|max:255',
                'nama_produk' => 'sometimes|required|string|max:255',
                'harga' => 'sometimes|required|numeric|min:0',
                'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'barcode' => 'nullable|string',
                'kategori_id' => 'nullable|exists:categories,id',
            ]);
            
            // Log data request untuk debugging
            Log::info('Update product request data:', $request->all());
            Log::info('Update product validated data:', $validatedData);

            \Log::info('Request Method:', ['method' => $request->method()]);
            \Log::info('Semua request data:', $request->all());

            if ($request->hasFile('gambar')) {
                \Log::info('File gambar terdeteksi!', [
                    'nama' => $request->file('gambar')->getClientOriginalName(),
                    'tipe' => $request->file('gambar')->getMimeType(),
                    'size' => $request->file('gambar')->getSize(),
                ]);
            } else {
                \Log::error('Gambar tidak terkirim dengan benar!');
            }
            
            // Handle file upload jika ada
            if ($request->hasFile('gambar')) {
                // Hapus file lama jika ada
                if ($product->gambar && Storage::disk('public')->exists($product->gambar)) {
                    Storage::disk('public')->delete($product->gambar);
                }
                
                $image = $request->file('gambar');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('products', $imageName, 'public');
                $validatedData['gambar'] = $path;
            }
            
            // Update product dengan data yang sudah divalidasi
            $product->forceFill($validatedData)->save();
            
            // Refresh model untuk mendapatkan data terbaru
            $product = Products::findOrFail($product->id);
            
            // Format response data
            $responseData = $product->toArray();
            
            // Tambahkan URL gambar jika ada
            if ($product->gambar) {
                $responseData['gambar_url'] = asset('storage/' . $product->gambar);
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Produk berhasil diupdate',
                'data' => $responseData
            ], Response::HTTP_OK);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
            
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
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
