<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use App\Models\Product;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\TransactionItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

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
            'transaction_item_id' => 'required|exists:transaction_items,id',
            'user_id' => 'required|exists:users,id',
            'nomor_invoice' => 'required|string|unique:transactions,nomor_invoice|max:255',
            'payment_method' => 'required|string|in:tunai,qris,transfer'
        ]);

        // Get transaction item with its product
        $transactionItem = TransactionItems::with('product')->findOrFail($validatedData['transaction_item_id']);
        
        // Calculate amounts
        $subtotal = $transactionItem->quantity * $transactionItem->product->harga;
        $ppn = $subtotal * 0.11; // 11% PPN
        $total = $subtotal + $ppn;

        // Create transaction
        $transaction = Transactions::create([
            'transaction_item_id' => $validatedData['transaction_item_id'],
            'user_id' => $validatedData['user_id'],
            'nomor_invoice' => $validatedData['nomor_invoice'],
            'payment_method' => $validatedData['payment_method'],
            'subtotal' => $subtotal,
            'ppn' => $ppn,
            'total' => $total
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction created successfully',
            'data' => $transaction->load('items.product')
        ], Response::HTTP_CREATED);
    }

    public function show(Transactions $transaction): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Transaction details',
            'data' => $transaction->load(['user'])
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Transactions $transaction): JsonResponse
    {
        $validatedData = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'transaction_item_id' => 'required|exists:transaction_items,id',
            'nomor_invoice' => 'required|string|unique:transactions,nomor_invoice,' . $transaction->id . '|max:255',
            'payment_method' => 'required|string|in:tunai,qris,transfer'
        ]);

        // Ambil transaction item
        $transactionItem = TransactionItems::with('product')->findOrFail($validatedData['transaction_item_id']);
        
        // Hitung subtotal, ppn, dan total
        $subtotal = $transactionItem->quantity * $transactionItem->product->harga;
        $ppn = $subtotal * 0.11; // 11% PPN
        $total = $subtotal + $ppn;

        // Tambahkan nilai ke data transaksi
        $validatedData['subtotal'] = $subtotal;
        $validatedData['ppn'] = $ppn;
        $validatedData['total'] = $total;

        $transaction->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaction updated successfully',
            'data' => $transaction->fresh()->load('transactionItem.product')
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

    public function printNota(Request $request, $id)
    {
        $transaction = Transactions::with(['user', 'items'])->findOrFail($id);
        
        // Format nota
        if (!$transaction->items || $transaction->items->count() === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada item dalam transaksi ini'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Format nota
        $nota = $this->formatNota($transaction);
        
        // Cek apakah ada printer_id di request
        $printerId = $request->input('printer_id');
        
        try {
            // Jika tidak ada printer_id, gunakan printer default
            if (!$printerId) {
                $printer = Printer::where('is_active', true)->where('is_active', true)->first();
                if (!$printer) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tidak ada printer default yang aktif'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                $printer = Printer::where('id', $printerId)->where('is_active', true)->first();
                if (!$printer) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Printer tidak ditemukan atau tidak aktif'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
            
            // Return data untuk dicetak oleh frontend
            return response()->json([
                'status' => 'success',
                'message' => 'Nota siap dicetak',
                'data' => [
                    'nota_text' => $nota,
                    // 'printer' => $printer,
                    'transaction' => $transaction
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mempersiapkan nota: ' . $e->getMessage()
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
        
        // Items
        $total = 0;
        if ($transaction->items && $transaction->items->count() > 0) {
            foreach ($transaction->items as $item) {
                // Pastikan item itu sendiri valid dan bukan boolean
                if (is_object($item)) {
                    // Lalu cek product-nya
                    if (isset($item->product) && is_object($item->product)) {
                        $nota .= $item->product->nama_produk . "\n";
                        $nota .= $item->quantity . " x " . number_format($item->product->harga, 0, ',', '.') . "\n";
                        $subtotal = $item->quantity * $item->product->harga;
                        $nota .= "     " . number_format($subtotal, 0, ',', '.') . "\n";
                        $total += $subtotal;
                    } else {
                        Log::warning('Product tidak valid untuk item ID: ' . $item->id);
                        // Handle missing product
                    }
                } else {
                    Log::warning('Item tidak valid dalam transaction ID: ' . $transaction->id);
                }
            }
        }
        
        // Footer
        $nota .= "--------------------------------\n" ; 
        // Gunakan $total yang sudah dihitung, bukan mengakses dari $item
        $nota .= "Total: Rp " . number_format($transaction->total, 0, ',', '.') . "\n";
        
        return $nota;
    }


    public function printTest(Request $request)
    {
        $printerId = $request->input('printer_id');
        
        try {
            // Jika tidak ada printer_id, gunakan printer default
            if (!$printerId) {
                $printer = Printer::where('is_active', true)->where('is_active', true)->first();
                if (!$printer) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tidak ada printer default yang aktif'
                    ], Response::HTTP_BAD_REQUEST);
                }
            } else {
                $printer = Printer::where('id', $printerId)->where('is_active', true)->first();
                if (!$printer) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Printer tidak ditemukan atau tidak aktif'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
            
            // Buat test print
            $testPrint = "================================\n";
            $testPrint .= "          TEST PRINT           \n";
            $testPrint .= "================================\n";
            $testPrint .= "Printer: " . $printer->name . "\n";
            $testPrint .= "Waktu: " . now()->format('d/m/Y H:i:s') . "\n";
            $testPrint .= "--------------------------------\n";
            $testPrint .= "Printer berfungsi dengan baik!\n";
            $testPrint .= "================================\n";
            
            return response()->json([
                'status' => 'success',
                'message' => 'Test print siap dicetak',
                'data' => [
                    // 'nota_text' => $testPrint,
                    'printer' => $printer
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mempersiapkan test print: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
