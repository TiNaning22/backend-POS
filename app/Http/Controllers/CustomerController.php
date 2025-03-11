<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::with('toko')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all customers',
            'data' => $customers
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_customer' => 'required|string|max:255',
            // 'toko_id' => 'nullable|exists:tokos,id',
        ]);

        $customer = Customer::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'data' => $customer
        ], Response::HTTP_CREATED);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Customer details',
            'data' => $customer->load('toko')
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_customer' => 'required|string|max:255',
            // 'toko_id' => 'nullable|exists:tokos,id',
        ]);

        $customer->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully',
            'data' => $customer
        ], Response::HTTP_OK);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully'
        ], Response::HTTP_OK);
    }
}
