<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // public function login(Request $request)
    // {
    //     try {
    //         $credentials = $request->validate([
    //             'email' => 'required|email',
    //             'password' => 'required'
    //         ]);

    //         if (!Auth::attempt($credentials)) {
    //             return response()->json(['message' => 'Las credenciales no son válidas'], 401);
    //         }

    //         $user = Auth::user();
    //         $tokenResult = $user->createToken('Personal Access Token');
    //         $token = $tokenResult->accessToken;

    //         return response()->json([
    //             'token' => $token,
    //             'expires_at' => $tokenResult->token->expires_at->toDateTimeString()
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Error al iniciar sesión'], 500);
    //     }
    // }
}
