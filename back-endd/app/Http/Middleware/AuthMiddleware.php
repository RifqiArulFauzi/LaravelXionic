<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $decodedToken = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
            //dd($decodedToken);
            // Anda juga bisa melakukan validasi tambahan seperti
            // memeriksa apakah token belum kadaluwarsa, dsb.

            return $next($request);
        } catch (ExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
