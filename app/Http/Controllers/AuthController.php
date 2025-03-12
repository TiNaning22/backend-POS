<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required',
        ]);

        $user = User::where('name', $request->name)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Hapus token lama jika ada
        $user->tokens()->delete();

        // Buat token dengan kemampuan berdasarkan role
        $abilities = [];
        switch ($user->role) {
            case 'admin':
                $abilities = ['admin'];
                break;
            case 'kasir':
                $abilities = ['kasir'];
                break;
            default:
                $abilities = ['default'];
        }

        $token = $user->createToken('auth-token', $abilities)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ],
            'token' => $token,
        ]); 
    }

    public function logout(Request $request)
    {
        try {
            // Hapus token saat ini jika ada
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }
            
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            // Jika token sudah kedaluwarsa atau tidak valid
            return response()->json(['message' => 'Logout successful (session already expired)']);
        }
    }
}
