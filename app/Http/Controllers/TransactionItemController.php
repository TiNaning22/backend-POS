<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionItems;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class TransactionItemController extends Controller
{
    /**
     * Display a listing of transaction items.
     */
    public function index()
    {
        // return response()->json(TransactionItems::all(), Response::HTTP_OK);

        $transactionItem = TransactionItems::with('transactions', 'product')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all transaksi',
            'data' => $transactionItem
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created transaction item in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0',
        ]);

        $transactionItem = TransactionItems::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction item created successfully',
            'data' => $transactionItem
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified transaction item.
     */
    public function show($id)
    {
        $transactionItem = TransactionItems::find($id);
        if (!$transactionItem) {
            return response()->json(['message' => 'Transaction item not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($transactionItem, Response::HTTP_OK);
    }

    /**
     * Update the specified transaction item in storage.
     */
    public function update(Request $request, $id)
    {
        $transactionItem = TransactionItems::find($id);
        if (!$transactionItem) {
            return response()->json(['message' => 'Transaction item not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'harga' => 'required|numeric|min:0',
        ]);

        $transactionItem->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction item updated successfully',
            'data' => $transactionItem
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified transaction item from storage.
     */
    public function destroy($id)
    {
        $transactionItem = TransactionItems::find($id);
        if (!$transactionItem) {
            return response()->json(['message' => 'Transaction item not found'], Response::HTTP_NOT_FOUND);
        }

        $transactionItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction item deleted successfully'
        ], Response::HTTP_OK);
    }
}
