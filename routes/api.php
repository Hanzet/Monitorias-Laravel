<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas (sin autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    // Información del usuario autenticado
    Route::get('/me', [AuthController::class, 'me']);
    
    // Gestión de sesiones
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Ruta de ejemplo protegida
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado',
            'data' => [
                'user' => $request->user()
            ]
        ]);
    });
});

// Mantener la ruta existente del UserController para compatibilidad
Route::post('/login-old', [UserController::class, 'login']);