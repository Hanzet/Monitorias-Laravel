<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$request->bearerToken()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de acceso requerido',
                    'error' => 'UNAUTHORIZED'
                ], 401);
            }

            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido o expirado',
                    'error' => 'INVALID_TOKEN'
                ], 401);
            }

            return $next($request);

        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
                'error' => 'AUTHENTICATION_FAILED'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación',
                'error' => 'AUTHENTICATION_ERROR'
            ], 500);
        }
    }
} 