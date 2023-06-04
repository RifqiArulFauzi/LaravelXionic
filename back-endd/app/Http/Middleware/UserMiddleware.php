<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class UserMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        try {
            //request jwt token 
            $jwt = $request->bearerToken();

            //decode jwt terus kondisi jika kolom role = User dia dizinkan kalo tidak unauthorized
            $decoded = JWT::decode($jwt, new Key(env('JWT_SECRET'), 'HS256'));
            if ($decoded->role == 'User'){
                
                    $user = User::find($decoded->id);
                    if (!$user) {
                        throw new UnauthorizedHttpException('Unauthorized');
                    }
    
                    // Menyimpan user ke dalam instance Request untuk penggunaan selanjutnya
                    $request->merge(['user' => $user]);
                    return $next($request);
            } else{
                return response()->json('Unauthorized', 401);
            }
        } catch (ExpiredException $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}