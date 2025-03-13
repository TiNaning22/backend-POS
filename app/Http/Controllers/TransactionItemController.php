<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\TransactionItems;
use App\Models\Products;
use App\Models\Transactions;
use App\Models\Inventory;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class TransactionItemController extends Controller
{


    /**
     * Display a listing of transaction items.
     */
    public function index()
    {
        // return response()->json(TransactionItems::all(), Response::HTTP_OK);

        $transactionItem = TransactionItems::with('transaction', 'product')->get();

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
        ]);

        // Ambil harga product
        $product = Products::findOrFail($validatedData['product_id']);
        $validatedData['harga'] = $product->harga;

        $lastInventory = Inventory::where('product_id', $validatedData['product_id'])
        ->orderBy('created_at', 'desc')
        ->first();

        if (!$lastInventory || $lastInventory->stok_akhir < $validatedData['quantity']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok produk tidak mencukupi'
            ], Response::HTTP_BAD_REQUEST);
        }

        $transactionItem = TransactionItems::create($validatedData);

        //total
        $transaction = Transactions::findOrFail($validatedData['transaction_id']);
        $newTotal = $transaction->total + ($validatedData['quantity'] * $validatedData['harga']);
        $transaction->update(['total' => $newTotal]);

        // Update inventory (kurangi stok)
        Inventory::create([
            'product_id' => $validatedData['product_id'],
            'tanggal' => now(),
            'stok_awal' => $lastInventory->stok_akhir,
            'stok_masuk' => 0,
            'stok_keluar' => $validatedData['quantity'],
            'stok_akhir' => $lastInventory->stok_akhir - $validatedData['quantity'],
            'keterangan' => 'Pengurangan stok dari transaksi #' . $transaction->nomor_invoice
        ]);

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
