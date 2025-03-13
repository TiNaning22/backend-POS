<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class TransactionController extends Controller
{

    public function index(): JsonResponse
    {
        $transactions = Transactions::with(['customer', 'user'])->get();

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
            // 'toko_id' => 'required|exists:tokos,id',
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
            // 'toko_id' => 'required|exists:tokos,id',
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

    public function printNota($id)
    {
        $transaction = Transactions::with(['user', 'items.product'])->findOrFail($id);
        
        // Format nota
        if (!$transaction->items || $transaction->items->count() === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada item dalam transaksi ini'
            ], Response::HTTP_BAD_REQUEST);
        }

        $nota = $this->formatNota($transaction);
        
        try {
            // Kirim ke printer bluetooth
            return response()->json([
                'status' => 'success',
                'message' => 'Nota berhasil dicetak',
                'data' => $nota
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mencetak nota: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function formatNota($transaction)
    {
        $nota = "";
        
        // Header
        $nota .= "No Invoice: " . $transaction->nomor_invoice . "\n"; 
        $nota .= "Tanggal: " . $transaction->created_at->format('d/m/Y H:i') . "\n";
        $nota .= "Kasir: " . $transaction->user->name . "\n";
        // if ($transaction->customer) {
        //     $nota .= "Customer: " . $transaction->customer->nama . "\n";
        // }
        // $nota .= "--------------------------------\n";
        
        // Items
        $total = 0;
        if ($transaction->items && $transaction->items->count() > 0) {
            foreach ($transaction->items as $item) {
                $nota .= $item->product->nama_produk . "\n";
                $nota .= $item->quantity . " x " . number_format($item->product->harga, 0, ',', '.') . "\n";
                $subtotal = $item->quantity * $item->product->harga;
                $nota .= "     " . number_format($subtotal, 0, ',', '.') . "\n";
                $total += $subtotal;
            }
        }
        
        // Footer
        $nota .= "--------------------------------\n";
        $nota .= "Total: Rp " . number_format($item->total, 0, ',', '.') . "\n";
        
        return $nota;
    }

}
