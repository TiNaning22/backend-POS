<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class OutletController extends Controller
{
    public function index(): JsonResponse
    {
        $outlets = Outlet::all();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all Outlet',
            'data' => $outlets
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);

        $outlet = Outlet::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Toko created successfully',
            'data' => $outlet
        ], Response::HTTP_CREATED);
    }

    public function show(Outlet $outlet): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'outlet details',
            'data' => $outlet
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Outlet $outlet): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_outlet' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);

        $outlet->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'outlet updated successfully',
            'data' => $outlet
        ], Response::HTTP_OK);
    }

    public function destroy(Outlet $outlet): JsonResponse
    {
        $outlet->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'outlet deleted successfully'
        ], Response::HTTP_OK);
    }
}
