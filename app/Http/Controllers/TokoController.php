<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class TokoController extends Controller
{
    public function index(): JsonResponse
    {
        $tokos = Toko::all();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all toko',
            'data' => $tokos
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_toko' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);

        $toko = Toko::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Toko created successfully',
            'data' => $toko
        ], Response::HTTP_CREATED);
    }

    public function show(Toko $toko): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Toko details',
            'data' => $toko
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Toko $toko): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_toko' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);

        $toko->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Toko updated successfully',
            'data' => $toko
        ], Response::HTTP_OK);
    }

    public function destroy(Toko $toko): JsonResponse
    {
        $toko->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Toko deleted successfully'
        ], Response::HTTP_OK);
    }
}
