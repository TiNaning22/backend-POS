<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerDiskon;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use SebastianBergmann\CodeCoverage\Report\Html\CustomCssFile;

class DiskonController extends Controller
{
    /**
     * Display a listing of the customer discounts.
     */
    public function index()
    {
        $diskon = CustomerDiskon::with('customer')->get();
        
        return response()->json([
            'status' => 'success',
            'message' => 'List of all diskon',
            'data' => $diskon
        ], Response::HTTP_OK);

    }

    /**
     * Store a newly created customer discount in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'persen_diskon' => 'required|numeric|min:0|max:100',
        ]);

        $diskon = CustomerDiskon::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer diskon created successfully',
            'data' => $diskon
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified customer discount.
     */
    public function show($id)
    {
        $diskon = CustomerDiskon::find($id);
        if (!$diskon) {
            return response()->json(['message' => 'Customer diskon not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($diskon, Response::HTTP_OK);
    }

    /**
     * Update the specified customer discount in storage.
     */
    public function update(Request $request, $id)
    {
        $diskon = CustomerDiskon::find($id);
        if (!$diskon) {
            return response()->json(['message' => 'Customer diskon not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'persen_diskon' => 'required|numeric|min:0|max:100',
        ]);

        $diskon->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer diskon updated successfully',
            'data' => $diskon
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified customer discount from storage.
     */
    public function destroy($id)
    {
        $diskon = CustomerDiskon::find($id);
        if (!$diskon) {
            return response()->json(['message' => 'Customer diskon not found'], Response::HTTP_NOT_FOUND);
        }

        $diskon->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer diskon deleted successfully'
        ], Response::HTTP_OK);
    }
}
