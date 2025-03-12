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
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'date' => 'required|date_format:Y-m-d',
            'is_active' => 'boolean',
        ]);

        $existingShift = Shift::where('user_id', $validated['user_id'])
            ->where('date', $validated['date'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                    ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
            })
            ->first();
            
        if ($existingShift) {
            return response()->json(['message' => 'Kasir sudah ada di waktu ini'], 400);
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
            'start_time' => 'sometimes|date_format:H:i:s',
            'end_time' => 'sometimes|date_format:H:i:s',
            'date' => 'sometimes|date_format:Y-m-d',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['start_time']) || isset($validated['end_time']) || isset($validated['date'])) {
            $start = $validated['start_time'] ?? $shift->start_time;
            $end = $validated['end_time'] ?? $shift->end_time;
            $date = $validated['date'] ?? $shift->date;
            
            $existingShift = Shift::where('user_id', $shift->user_id)
                ->where('id', '!=', $shift->id)
                ->where('date', $date)
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('start_time', [$start, $end])
                        ->orWhereBetween('end_time', [$start, $end]);
                })
                ->first();
                
            if ($existingShift) {
                return response()->json(['message' => 'Kasir sudah ada di waktu ini'], 400);
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
