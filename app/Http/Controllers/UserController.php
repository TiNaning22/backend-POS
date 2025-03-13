<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('outlet')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of all pengguna',
            'data' => $users
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:5',
            'role' => ['required', Rule::in(['superadmin', 'admin', 'kasir'])],
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna created successfully',
            'data' => $user
        ], Response::HTTP_CREATED);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna details',
            'data' => $user->load('outlet')
        ], Response::HTTP_OK);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:5',
            'role' => ['required', Rule::in(['superadmin', 'admin', 'kasir'])],
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($request->password);
        } else {
            unset($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna updated successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Pengguna deleted successfully'
        ], Response::HTTP_OK);
    }
}
