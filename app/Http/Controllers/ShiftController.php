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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jadwal' => 'required|in:siang,malam',
            'date' => 'required|date_format:Y-m-d',
            'is_active' => 'boolean',
        ]);

        $existingShift = Shift::where('user_id', $validated['user_id'])
        ->where('date', $validated['date'])
        ->where('jadwal', $validated['jadwal'])
        ->first();

        if ($existingShift) {
            return response()->json(['message' => 'Kasir sudah ada di jadwal ini'], 400);
        }

        $shift = Shift::create($validated);

        return response()->json([
            'status' => 'success',
           'message' => 'Shift kasir berhasil ditambahkan',
            'data' => $shift
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $shift = Shift::with(['user'])->find($id);
    
        if (!$shift) {
            return response()->json([
                'error' => 'Data tidak ditemukan',
                'message' => 'Shift dengan ID yang diminta tidak tersedia'
            ], 404);
        }
        
        return response()->json(['data' => $shift]);
    }

    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'jadwal' => 'required|in:siang,malam',
            'date' => 'sometimes|date_format:Y-m-d',
            'is_active' => 'sometimes|boolean',
        ]);
    
        if (isset($validated['jadwal']) || isset($validated['date'])) {
            $jadwal = $validated['jadwal'] ?? $shift->jadwal;
            $date = $validated['date'] ?? $shift->date;
            
            $existingShift = Shift::where('user_id', $shift->user_id)
                ->where('id', '!=', $shift->id)
                ->where('date', $date)
                ->where('jadwal', $jadwal)
                ->first();
                
            if ($existingShift) {
                return response()->json(['message' => 'Kasir sudah ada di jadwal ini'], 400);
            }
        }
    
        $shift->update($validated);
    
        return response()->json([
           'status' => 'success',
           'message' => 'Shift kasir berhasil diubah',
            'data' => $shift
        ], Response::HTTP_OK);
    }
    

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return response()->json([
           'status' => 'success',
           'message' => 'Shift kasir berhasil dihapus'
        ], Response::HTTP_OK);
    }
}
