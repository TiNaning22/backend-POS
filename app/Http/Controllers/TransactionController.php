<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Transactions;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Products;
use App\Models\TransactionItems;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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
            'user_id' => 'required|exists:users,id',
            'outlet_id' => 'required|exists:outlets,id',
            'total' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
            // 'nomor_invoice' => 'required|string|unique:transactions,nomor_invoice|max:255',
        ]);

        try {
            DB::beginTransaction();

            $nomor_invoice = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            $validatedData['nomor_invoice'] = $nomor_invoice;


            foreach ($request->items as $item) {
                $storeProduct = Inventory::where('outlet_id', $request->outlet_id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$storeProduct || $storeProduct->stock_akhir < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient stock for product ID: ' . $item['product_id']
                    ], 400);
                }

                $itemSubtotal = $item['harga'] * $item['quantity'];
            }

            $validatedData['status'] = 'selesai';
            $validatedData['total'] = $itemSubtotal;
            $transaction = Transactions::create($validatedData);

            foreach ($request->items as $item) {
                TransactionItems::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity
                ]);

                Inventory::create([
                    'outlet_id' => $validatedData['outlet_id'],
                    'product_id' => $validatedData['product_id'],
                    'tanggal' => date(now()),
                    'stok_keluar' => $item->quantity,
                    'stok_akhir' => $item->sdsa,
                    'keterangan' => 'Transaksi pada: ' . $transaction->id,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction created successfully',
                'data' => $transaction
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'success',
                'message' => $th->getMessage(),
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
}
