<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::with('product')->get();

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ], 200);
    }
}
