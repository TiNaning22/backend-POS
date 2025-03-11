<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function index(): JsonResponse
    {
        $transactions = Transactions::with(['customer', 'user', 'toko'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all transactions',
            'data' => $transactions
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'toko_id' => 'required|exists:tokos,id',
            // 'total' => 'required|numeric|min:0',
            'nomor_invoice' => 'required|string|unique:transactions,nomor_invoice|max:255',
        ]);

        $validatedData['total'] = 0;

        $transaction = Transactions::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction created successfully',
            'data' => $transaction
        ], Response::HTTP_CREATED);
    }

    public function show(Transactions $transaction): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Transaction details',
            'data' => $transaction->load(['customer', 'user', 'toko'])
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Transactions $transaction): JsonResponse
    {
        $validatedData = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'toko_id' => 'required|exists:tokos,id',
            'total' => 'required|numeric|min:0',
            'nomor_invoice' => 'required|string|unique:transactions,nomor_invoice,' . $transaction->id . '|max:255',
        ]);

        $transaction->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction updated successfully',
            'data' => $transaction
        ], Response::HTTP_OK);
    }

    public function destroy(Transactions $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction deleted successfully'
        ], Response::HTTP_OK);
    }
}
