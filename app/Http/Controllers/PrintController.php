<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class PrintController extends Controller
{
    public function index()
    {
        $printer = Printer::all();

        return response()->json([
            'status' => 'success',
            'message' => 'List Semua Printer',
            'data' => $printer
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_printer' => 'required|string|max:255',
            'connection_type' => 'required|string|in:bluetooth,usb,network',
            'printer_type' => 'required|string|in:thermal,inkjet,laser,dot_matrix',
            'deskripsi' => 'nullable|text',
            'is_active' => 'boolean',

        ]);

        $printer = Printer::create($validatedData);

        return response()->json([
           'status' => 'success',
           'message' => 'Printer berhasil ditambahkan',
            'data' => $printer
        ], Response::HTTP_CREATED);  
    }

    public function show($id)
    {
        $printer = Printer::find($id);

        if (!$printer) {
            return response()->json([
                'error' => 'Printer tidak ditemukan',
               'message' => 'Printer tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
           'message' => 'Detail Printer',
            'data' => $printer
        ], Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        $printer = Printer::find($id);

        if (!$printer) {
            return response()->json([
               'error' => 'Printer tidak ditemukan',
               'message' => 'Printer tidak ditemukan'
            ], 404);
        }

        $validatedData = $request->validate([
            'nama_printer' => 'required|string|max:255',
            'connection_type' => 'required|string|in:bluetooth,usb,network',
            'printer_type' => 'required|string|in:thermal,inkjet,laser,dot_matrix',
            'deskripsi' => 'nullable|text',
            'is_active' => 'boolean',

        ]);

        $printer->update($validatedData);
    }

    public function destroy($id)
    {
        $printer = Printer::find($id);

        if (!$printer) {
            return response()->json([
                'error' => 'Printer tidak ditemukan',
               'message' => 'Printer tidak ditemukan'
            ], 404);
        }

        $printer->delete();

        return response()->json([
           'status' => 'success',
           'message' => 'Printer berhasil dihapus'
        ], Response::HTTP_NO_CONTENT);
    }


}
