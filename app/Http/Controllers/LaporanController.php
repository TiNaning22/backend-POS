<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Transactions;
use App\Models\TransactionItems;
use App\Models\Products;
use App\Models\Outlet;
// use App\Models\ProdukOutlet;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        
        // Query dasar
        $query = Products::with(['category']);
        
        // Filter berdasarkan toko
        // if ($outletId) {
        //     $query->where('outlet_id', $outletId);
        // } elseif (!$user->isSuperAdmin()) {
        //     $query->where('outlet_id', $user->outlet_id);
        // }
        
        // Filter stok rendah (kurang dari 10)
        if ($lowStock) {
            $query->where('stock', '<', 10);
        }
        
        // Filter berdasarkan kategori
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Sorting
        if ($sortBy === 'name') {
            $query->orderBy('name', $sortOrder);
        } elseif ($sortBy === 'stock') {
            $query->orderBy('stock', $sortOrder);
        } elseif ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        }
        
        // Eksekusi query
        $products = $query->get();
        
        // Hitung summary
        $totalProducts = $products->count();
        $totalStock = $products->sum('stock');
        $lowStockCount = $products->where('stock', '<', 10)->count();
        $outOfStockCount = $products->where('stock', 0)->count();
        
        // Dapatkan informasi toko jika ada outletId
        $outlet = null;
        if ($outletId) {
            $outlet = Outlet::find($outletId);
        }
        
        return response()->json([
            'data' => $products,
            'outlet' => $outlet,
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
}
