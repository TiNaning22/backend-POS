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
        $user = Auth::user();
        $outletId = $request->query('outlet_id');
        
        // Validasi akses
        // if (!$user->isSuperAdmin() && $user->outlet_id != $outletId) {
        //     return response()->json(['message' => 'Anda hanya dapat melihat laporan toko Anda sendiri'], 403);
        // }
        
        // Dapatkan parameter filter
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $groupBy = $request->query('group_by', 'day'); // day, week, month
        
        // Query dasar
        $query = Transactions::query()
            ->selectRaw('SUM(total) as total_omset, COUNT(*) as total_transactions');
        
        // Filter berdasarkan toko
        // if ($outletId) {
        //     $query->where('outlet_id', $outletId);
        // } elseif (!$user->isSuperAdmin()) {
        //     $query->where('outlet_id', $user->outlet_id);
        // }
        
        // Filter berdasarkan tanggal
        $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        // Grouping berdasarkan periode
        if ($groupBy === 'day') {
            $query->selectRaw('DATE(created_at) as period')
                ->groupBy(DB::raw('DATE(created_at)'));
        } elseif ($groupBy === 'week') {
            $query->selectRaw('YEAR(created_at) as year, WEEK(created_at) as week, 
                            CONCAT(YEAR(created_at), "-W", LPAD(WEEK(created_at), 2, "0")) as period')
                ->groupBy(DB::raw('YEAR(created_at), WEEK(created_at)'));
        } elseif ($groupBy === 'month') {
            $query->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, 
                            CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0")) as period')
                ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'));
        }
        
        $query->orderBy('period');
        
        // Eksekusi query
        $omsetData = $query->get();
        
        // Query produk terlaris
        $bestSellingProductsQuery = TransactionItems::query()
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->selectRaw('products.id, products.nama_produk, SUM(transaction_items.quantity) as total_sold, SUM(transactions.total) as total_revenue')
            ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
        // Filter berdasarkan toko untuk produk terlaris
        // if ($outletId) {
        //     $bestSellingProductsQuery->where('transactions.outlet_id', $outletId);
        // } elseif (!$user->isSuperAdmin()) {
        //     $bestSellingProductsQuery->where('transactions.outlet_id', $user->outlet_id);
        // }
        
        // Produk terlaris per periode
        $bestSellingProductsByPeriod = [];
        
        if ($groupBy === 'day') {
            // Untuk tampilan harian tidak perlu breakdown per hari karena terlalu detail
            // Cukup tampilkan top products untuk seluruh periode
            
        } elseif ($groupBy === 'week') {
            // Produk terlaris per minggu
            $weeklyProducts = DB::table('transaction_items')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->selectRaw('products.id, products.nama_produk, 
                            YEAR(transactions.created_at) as year, 
                            WEEK(transactions.created_at) as week,
                            CONCAT(YEAR(transactions.created_at), "-W", LPAD(WEEK(transactions.created_at), 2, "0")) as period,
                            SUM(transaction_items.quantity) as total_sold, 
                            SUM(transaction_items.total) as total_revenue')
                ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy('products.id', 'period')
                ->orderBy('period')
                ->orderBy('total_sold', 'desc')
                ->get();
                
            // Mengelompokkan produk terlaris per minggu
            $weeklyProductsGrouped = [];
            foreach ($weeklyProducts as $product) {
                if (!isset($weeklyProductsGrouped[$product->period])) {
                    $weeklyProductsGrouped[$product->period] = [];
                }
                if (count($weeklyProductsGrouped[$product->period]) < 5) { // Ambil 5 produk terlaris
                    $weeklyProductsGrouped[$product->period][] = $product;
                }
            }
            $bestSellingProductsByPeriod = $weeklyProductsGrouped;
            
        } elseif ($groupBy === 'month') {
            // Produk terlaris per bulan
            $monthlyProducts = DB::table('transaction_items')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->selectRaw('products.id, products.nama_produk,
                            YEAR(transactions.created_at) as year, 
                            MONTH(transactions.created_at) as month,
                            CONCAT(YEAR(transactions.created_at), "-", LPAD(MONTH(transactions.created_at), 2, "0")) as period,
                            SUM(transaction_items.quantity) as total_sold, 
                            SUM(transaction_items.total) as total_revenue')
                ->whereBetween('transactions.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                ->groupBy('products.id', 'period')
                ->orderBy('period')
                ->orderBy('total_sold', 'desc')
                ->get();
                
            // Mengelompokkan produk terlaris per bulan
            $monthlyProductsGrouped = [];
            foreach ($monthlyProducts as $product) {
                if (!isset($monthlyProductsGrouped[$product->period])) {
                    $monthlyProductsGrouped[$product->period] = [];
                }
                if (count($monthlyProductsGrouped[$product->period]) < 5) { // Ambil 5 produk terlaris
                    $monthlyProductsGrouped[$product->period][] = $product;
                }
            }
            $bestSellingProductsByPeriod = $monthlyProductsGrouped;
        }
        
        // Produk terlaris overall untuk periode yang dipilih
        $bestSellingProducts = $bestSellingProductsQuery
            ->groupBy('products.id', 'products.nama_produk')
            ->orderBy('total_sold', 'desc')
            ->limit(10)  // Ambil 10 produk terlaris
            ->get();
        
        // Dapatkan informasi toko jika ada outletId
        $outlet = null;
        if ($outletId) {
            $outlet = Outlet::find($outletId);
        }
        
        // Hitung total
        $totalOmset = $omsetData->sum('total_omset');
        $totalTransactions = $omsetData->sum('total_transactions');
        
        return response()->json([
            'data' => $omsetData,
            'outlet' => $outlet,
            'best_selling_products' => [
                'overall' => $bestSellingProducts,
                'by_period' => $bestSellingProductsByPeriod
            ],
            'summary' => [
                'total_omset' => $totalOmset,
                'total_transactions' => $totalTransactions,
                'average_per_transaction' => $totalTransactions > 0 ? $totalOmset / $totalTransactions : 0,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => $groupBy
            ]
        ]);
    }

    public function stockBarang(Request $request)
    {
        $user = Auth::user();
        $outletId = $request->query('outlet_id');
        
        // // Validasi akses
        // if (!$user->isSuperAdmin() && $user->outlet_id != $outletId) {
        //     return response()->json(['message' => 'Anda hanya dapat melihat laporan toko Anda sendiri'], 403);
        // }
        
        // Parameter filter
        $lowStock = $request->query('low_stock', false);
        $categoryId = $request->query('category_id');
        $sortBy = $request->query('sort_by', 'stock');
        $sortOrder = $request->query('sort_order', 'asc');
        $date = $request->query('date', date('Y-m-d')); // Default ke hari ini
        
        // Query dasar untuk produk
        $query = Products::with(['category']);
        
        // Filter berdasarkan kategori
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Filter berdasarkan toko (jika implementasi multioutlet)
        // if ($outletId) {
        //     $query->where('outlet_id', $outletId);
        // } elseif (!$user->isSuperAdmin()) {
        //     $query->where('outlet_id', $user->outlet_id);
        // }
        
        // Dapatkan semua produk yang sesuai dengan filter kategori/outlet
        $products = $query->get();
        
        // Ambil data inventory terbaru untuk setiap produk
        $productInventories = [];
        $totalStock = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        
        foreach ($products as $product) {
            // Ambil data inventory terbaru untuk produk ini (sebelum atau sama dengan tanggal yang dipilih)
            $latestInventory = Inventory::where('product_id', $product->id)
                ->where('tanggal', '<=', $date)
                ->orderBy('tanggal', 'desc')
                ->first();
            
            // Jika tidak ada data inventory, anggap stok 0
            $currentStock = $latestInventory ? $latestInventory->stok_akhir : 0;
            
            // Tambahkan informasi stok ke produk
            $product->current_stock = $currentStock;
            
            // Hitung untuk summary
            $totalStock += $currentStock;
            if ($currentStock < 10) $lowStockCount++;
            if ($currentStock == 0) $outOfStockCount++;
            
            $productInventories[] = [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category ? $product->category->name : null,
                'price' => $product->price,
                'stock' => $currentStock,
                'last_updated' => $latestInventory ? $latestInventory->tanggal : null,
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
        
        $totalProducts = count($productInventories);
        
        return response()->json([
            'data' => array_values($productInventories), // Reset array keys
            'outlet' => $outlet,
            'date' => $date,
            'summary' => [
                'total_products' => $totalProducts,
                'total_stock' => $totalStock,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'average_stock_per_product' => $totalProducts > 0 ? $totalStock / $totalProducts : 0
            ]
        ]);
    }

    public function kasMasuk(Request $request)
    {
        $user = Auth::user();
        $outletId = $request->query('outlet_id');
        
        // Validasi akses
        // if (!$user->isSuperAdmin() && $user->outlet_id != $outletId) {
        //     return response()->json(['message' => 'Anda hanya dapat melihat laporan toko Anda sendiri'], 403);
        // }
        
        // Dapatkan parameter filter
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::now()->format('Y-m-d'));
        $groupBy = $request->query('group_by', 'day'); // day, shift, kasir
        
        // Query dasar untuk kas masuk (transaksi)
        $query = Transactions::query()
            ->selectRaw('SUM(total) as total_kas_masuk, COUNT(*) as total_transactions');
        
        // // Filter berdasarkan toko
        // if ($outletId) {
        //     $query->where('outlet_id', $outletId);
        // } elseif (!$user->isSuperAdmin()) {
        //     $query->where('outlet_id', $user->outlet_id);
        // }
        
        // Filter berdasarkan tanggal
        $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        
        // Grouping berdasarkan parameter
        if ($groupBy === 'day') {
            $query->selectRaw('DATE(created_at) as period')
                  ->groupBy(DB::raw('DATE(created_at)'));
        } elseif ($groupBy === 'shift') {
            $query->leftJoin('shifts', 'transactions.shift_id', '=', 'shifts.id')
                  ->selectRaw('shifts.name as period, shifts.id as shift_id')
                  ->groupBy('shifts.id', 'shifts.name');
        } elseif ($groupBy === 'kasir') {
            $query->join('users', 'transactions.user_id', '=', 'users.id')
                  ->selectRaw('users.name as period, users.id as user_id')
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
