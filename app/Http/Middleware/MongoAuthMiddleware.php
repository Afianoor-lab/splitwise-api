<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Token;
use App\Models\User;
use App\Helpers\ApiResponse;
use Carbon\Carbon;

class MongoAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken() ?? $request->header('token');

        if (!$token) {
            return ApiResponse::error('Unauthorized: Bearer token is missing.', 401);
        }

        $tokenRecord = Token::where('api_token', $token)->first();

        // Check karein token exist karta hai ya nahi
        if (!$tokenRecord) {
            return ApiResponse::error('Unauthorized: Invalid token.', 401);
        }

        // 2-Hour Expiry Check: Agar current time expires_at ke baad ka hai
        if ($tokenRecord->expires_at && Carbon::parse($tokenRecord->expires_at)->isPast()) {
            // Optional: Expired token ko clean up/delete kar sakte hain
            $tokenRecord->delete();
            return ApiResponse::error('Unauthorized: Token has expired after 2 hours. Please login again.', 401);
        }

        $user = User::find($tokenRecord->user_id);

        if (!$user) {
            return ApiResponse::error('Unauthorized: User record not found.', 401);
        }

        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}