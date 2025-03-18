<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Outlet;
use App\Models\Products;
use App\Models\Inventory;
use App\Models\Transactions;
// use App\Models\ProdukOutlet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\TransactionItems;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function outletRevenue(Request $request)
    {
        try {
            $user = Auth::user();
            $outletId = $request->query('outlet_id');
            
            $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
            $groupBy = $request->query('group_by', 'week');
            
            // Modified query to calculate total_omset from transaction_items
            $query = Transactions::query()
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id');
            
            if ($groupBy === 'day') {
                $query->selectRaw('DATE(transactions.created_at) as period')
                    ->selectRaw('SUM(transaction_items.quantity * transactions.total) as total_omset')
                    ->selectRaw('COUNT(DISTINCT transactions.id) as total_transactions')
                    ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->groupBy('period');
            } elseif ($groupBy === 'week') {
                $query->selectRaw('CONCAT(YEAR(transactions.created_at), "-W", LPAD(WEEK(transactions.created_at), 2, "0")) as period')
                    ->selectRaw('YEAR(transactions.created_at) as year')
                    ->selectRaw('WEEK(transactions.created_at) as week')
                    ->selectRaw('SUM(transaction_items.quantity * transactions.total) as total_omset')
                    ->selectRaw('COUNT(DISTINCT transactions.id) as total_transactions')
                    ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->groupBy('year', 'week', 'period');
            } elseif ($groupBy === 'month') {
                $query->selectRaw('CONCAT(YEAR(transactions.created_at), "-", LPAD(MONTH(transactions.created_at), 2, "0")) as period')
                    ->selectRaw('YEAR(transactions.created_at) as year')
                    ->selectRaw('MONTH(transactions.created_at) as month')
                    ->selectRaw('SUM(transaction_items.quantity * transactions.total) as total_omset')
                    ->selectRaw('COUNT(DISTINCT transactions.id) as total_transactions')
                    ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->groupBy('year', 'month', 'period');
            }
            
            if ($outletId) {
                $query->where('transactions.outlet_id', $outletId);
            }
            
            $query->orderBy('period');
            $omsetData = $query->get();
            
            $bestSellingProducts = Products::query()
                ->join('transaction_items', 'products.id', '=', 'transaction_items.product_id')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->selectRaw('products.id, products.nama_produk, SUM(transaction_items.quantity) as total_sold, SUM(transaction_items.quantity * products.harga) as total_revenue')
                ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
            if ($outletId) {
                $bestSellingProducts->where('transactions.outlet_id', $outletId);
            }
            
            $bestSellingProducts = $bestSellingProducts->groupBy('products.id', 'products.nama_produk')
                ->orderBy('total_sold', 'desc')
                ->limit(10)
                ->get();
            
            $outlet = $outletId ? Outlet::find($outletId) : null;
            
            $totalOmset = $omsetData->sum('total_omset');
            $totalTransactions = $omsetData->sum('total_transactions');
            
            return response()->json([
                'data' => $omsetData,
                'outlet' => $outlet,
                'best_selling_products' => $bestSellingProducts,
                'summary' => [
                    'total_omset' => $totalOmset,
                    'total_transactions' => $totalTransactions,
                    'average_per_transaction' => $totalTransactions > 0 ? $totalOmset / $totalTransactions : 0,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'group_by' => $groupBy
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan dalam mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    public function stockBarang(Request $request)
    {
        $user = Auth::user();
        $outletId = $request->query('outlet_id');
        
        // Parameter filter
        $lowStock = $request->query('low_stock', false);
        $categoryId = $request->query('category_id');
        $sortBy = $request->query('sort_by', 'stock');
        $sortOrder = $request->query('sort_order', 'asc');
        $date = $request->query('date', date('Y-m-d'));
        
        // Query dasar untuk produk
        $query = Products::with(['category']);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $products = $query->get();
        
        $productInventories = [];
        $totalStock = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        
        foreach ($products as $product) {
            // Ambil data inventory terbaru berdasarkan created_at, bukan hanya tanggal
            $latestInventory = Inventory::where('product_id', $product->id)
                ->whereDate('tanggal', '<=', $date)
                ->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc') // Tambahkan ordering by created_at
                ->first();
                
            // Jika tidak ada inventory, coba ambil inventory pertama sebagai stok awal
            if (!$latestInventory) {
                $latestInventory = Inventory::where('product_id', $product->id)
                    ->orderBy('tanggal', 'asc')
                    ->orderBy('created_at', 'asc') // Tambahkan ordering by created_at
                    ->first();
            }
            
            // Jika masih tidak ada inventory, gunakan stok 0
            $currentStock = $latestInventory ? $latestInventory->stok_akhir : 0;
            
            // Tambahkan informasi stok ke produk
            $product->current_stock = $currentStock;
            
            // Hitung untuk summary
            $totalStock += $currentStock;
            if ($currentStock < 10) $lowStockCount++;
            if ($currentStock == 0) $outOfStockCount++;
            
            $productInventories[] = [
                'id' => $product->id,
                'name' => $product->nama_produk,
                'category' => $product->category ? $product->category->name : null,
                'price' => $product->harga,
                'stock' => $currentStock,
                'last_updated' => $latestInventory ? $latestInventory->tanggal : null,
                'inventory_data' => $latestInventory, // Tambahkan data inventory untuk debugging
                'product_data' => $product
            ];
        }
        
        // Sorting hasil
        if ($sortBy === 'name') {
            usort($productInventories, function($a, $b) use ($sortOrder) {
                return $sortOrder === 'asc' ? 
                    strcmp($a['name'], $b['name']) : 
                    strcmp($b['name'], $a['name']);
            });
        } elseif ($sortBy === 'stock') {
            usort($productInventories, function($a, $b) use ($sortOrder) {
                return $sortOrder === 'asc' ? 
                    $a['stock'] - $b['stock'] : 
                    $b['stock'] - $a['stock'];
            });
        } elseif ($sortBy === 'price') {
            usort($productInventories, function($a, $b) use ($sortOrder) {
                return $sortOrder === 'asc' ? 
                    $a['price'] - $b['price'] : 
                    $b['price'] - $a['price'];
            });
        }
        
        // Filter stok rendah (kurang dari 10) setelah pengurutan
        if ($lowStock) {
            $productInventories = array_filter($productInventories, function($item) {
                return $item['stock'] < 10;
            });
        }
        
        // Dapatkan informasi toko jika ada outletId
        $outlet = null;
        if ($outletId) {
            $outlet = Outlet::find($outletId);
        }
        
        return response()->json([
            'data' => array_values($productInventories),
            'outlet' => $outlet,
            'date' => $date,
            'summary' => [
                'total_products' => count($productInventories),
                'total_stock' => $totalStock,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'average_stock_per_product' => count($productInventories) > 0 ? $totalStock / count($productInventories) : 0
            ]
        ]);
    }

    
    public function kasMasuk(Request $request)
    {
        $user = Auth::user();
        $outletId = $request->query('outlet_id');
        
        // Dapatkan parameter filter
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $groupBy = $request->query('group_by', 'shift'); // day, shift, kasir
        
        // Query dasar untuk kas masuk (transaksi) - Dimodifikasi untuk menggunakan join dengan transaction_items
        $query = Transactions::query()
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id');
        
        // Filter berdasarkan tanggal
        $query->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        // Filter berdasarkan toko
        if ($outletId) {
            $query->where('transactions.outlet_id', $outletId);
        }
        
        // Grouping berdasarkan parameter
        if ($groupBy === 'day') {
            $query->selectRaw('DATE(transactions.created_at) as period')
                  ->selectRaw('SUM(transaction_items.quantity * transactions.total) as total_kas_masuk')
                  ->selectRaw('COUNT(DISTINCT transactions.id) as total_transactions')
                  ->groupBy(DB::raw('DATE(transactions.created_at)'));
        } elseif ($groupBy === 'shift') {
            // Asumsikan bahwa jadwal shift terkait dengan waktu transaksi, bukan menggunakan shift_id
            // Misalnya: Pagi (6-12), Siang (12-18), Malam (18-24)
            $query->selectRaw("
                CASE 
                    WHEN HOUR(transactions.created_at) BETWEEN 6 AND 11 THEN 'Pagi (06:00-12:00)'
                    WHEN HOUR(transactions.created_at) BETWEEN 12 AND 17 THEN 'Siang (12:00-18:00)'
                    ELSE 'Malam (18:00-06:00)'
                END as period
            ")
                  ->selectRaw('SUM(transaction_items.quantity * transactions.total) as total_kas_masuk')
                  ->selectRaw('COUNT(DISTINCT transactions.id) as total_transactions')
                  ->groupBy('period');
        } elseif ($groupBy === 'kasir') {
            $query->join('users', 'transactions.user_id', '=', 'users.id')
                  ->selectRaw('users.name as period')
                  ->selectRaw('users.id as user_id')
                  ->selectRaw('SUM(transaction_items.quantity * transactions.total) as total_kas_masuk')
                  ->selectRaw('COUNT(DISTINCT transactions.id) as total_transactions')
                  ->groupBy('users.id', 'users.name');
        }
        
        // Eksekusi query
        $kasData = $query->get();
        
        // Dapatkan informasi toko jika ada outletId
        $outlet = null;
        if ($outletId) {
            $outlet = Outlet::find($outletId);
        }
        
        // Hitung total
        $totalKasMasuk = $kasData->sum('total_kas_masuk');
        $totalTransactions = $kasData->sum('total_transactions');
        
        return response()->json([
            'data' => $kasData,
            'outlet' => $outlet,
            'summary' => [
                'total_kas_masuk' => $totalKasMasuk,
                'total_transactions' => $totalTransactions,
                'average_per_transaction' => $totalTransactions > 0 ? $totalKasMasuk / $totalTransactions : 0,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => $groupBy
            ]
        ]);
    }

    public function downloadLaporan(Request $request, $jenis)
    {
            // Validasi jenis laporan
        if (!in_array($jenis, ['omset', 'stok', 'kasmasuk'])) {
            return response()->json(['message' => 'Jenis laporan tidak valid'], 400);
        }
        
        // Dapatkan data laporan sesuai jenis
        if ($jenis === 'omset') {
            $response = $this->outletRevenue($request);
        } elseif ($jenis === 'stok') {
            $response = $this->stockBarang($request);
        } else {
            $response = $this->kasMasuk($request);
        }
        
        // Konversi response menjadi array
        $responseData = json_decode($response->getContent(), true);
        
        // Cek apakah ada data
        if (!isset($responseData['data']) || empty($responseData['data'])) {
            return response()->json(['message' => 'Tidak ada data untuk dicetak'], 404);
        }
        
        $data = $responseData['data'];
        $summary = $responseData['summary'] ?? [];
        $outlet = $responseData['outlet'] ?? null;
        
        // Tentukan judul laporan
        $judulLaporan = "";
        if ($jenis === 'omset') {
            $judulLaporan = "Laporan Omset";
        } elseif ($jenis === 'stok') {
            $judulLaporan = "Laporan Stok Barang";
        } else {
            $judulLaporan = "Laporan Kas Masuk";
        }
        
        // Tambahkan nama outlet jika ada
        if ($outlet) {
            $judulLaporan .= " - " . $outlet['nama_outlet'];
        }
        
        // Tentukan periode laporan
        $periodeTeks = "";
        if (isset($summary['start_date']) && isset($summary['end_date'])) {
            $startDate = Carbon::parse($summary['start_date'])->format('d/m/Y');
            $endDate = Carbon::parse($summary['end_date'])->format('d/m/Y');
            $periodeTeks = "Periode: $startDate s/d $endDate";
        } elseif (isset($responseData['date'])) {
            $date = Carbon::parse($responseData['date'])->format('d/m/Y');
            $periodeTeks = "Tanggal: $date";
        }
        
        // Siapkan view untuk dirender sebagai PDF
        $view = view('reports.' . $jenis, [
            'data' => $data,
            'summary' => $summary,
            'outlet' => $outlet,
            'judul' => $judulLaporan,
            'periode' => $periodeTeks,
            'tanggal_cetak' => Carbon::now()->format('d/m/Y H:i')
        ]);
        
        // Buat nama file
        $fileName = $jenis . '_laporan_' . date('Y-m-d') . '.pdf';
        
        // Generate PDF menggunakan package seperti DomPDF atau lainnya
        $pdf = \PDF::loadView('reports.' . $jenis, [
            'data' => $data,
            'summary' => $summary,
            'outlet' => $outlet,
            'judul' => $judulLaporan,
            'periode' => $periodeTeks,
            'tanggal_cetak' => Carbon::now()->format('d/m/Y H:i')
        ]);
        
        // Return PDF untuk diunduh
        return $pdf->download($fileName);
    }
}
