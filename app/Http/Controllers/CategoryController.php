<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::all();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all categories',
            'data' => $categories
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $category = Category::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => $category
        ], Response::HTTP_CREATED);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Category details',
            'data' => $category
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validatedData = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $category->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully',
            'data' => $category
        ], Response::HTTP_OK);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ], Response::HTTP_OK);
    }
}
