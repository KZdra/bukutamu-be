<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Exception;

class JWTMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Autentikasi user dari token
            $user = JWTAuth::parseToken()->authenticate();

            // Ambil user_id langsung dari model user
            $userId = $user->id;

        } catch (TokenInvalidException $e) {
            return response()->json(['status' => 'Token is Invalid'], 401);
        } catch (TokenExpiredException $e) {
            return response()->json(['status' => 'Token is Expired'], 401);
        } catch (Exception $e) {
            return response()->json(['status' => 'Authorization Token not found'], 401);
        }

        // Lanjutkan request, inject userId ke request
        return $next($request->merge(['userId' => $userId]));
    }
}
