<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;

class FirebaseAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $idToken = $request->bearerToken();

        if (!$idToken) {
            return response()->json(['error' => 'トークンがありません'], 401);
        }

        try {
            $verifiedIdToken = app(FirebaseAuth::class)->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');

            // リクエストにUIDを追加(コントローラーで使用)
            $request->merge(['firebase_uid' => $uid]);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['error' => '認証に失敗しました'], 401);
        }
    }
}
