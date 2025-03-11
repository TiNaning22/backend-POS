<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ShiftController extends Controller
{
    public function index()
    {
        $shift = Shift::with('user')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all Shift kasir',
            'data' => $shift
        ], Response::HTTP_OK);
    }
}
