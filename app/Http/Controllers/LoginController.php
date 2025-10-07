<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;


class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $idToken = $request->bearerToken();
            $verifiedIdToken = app(FirebaseAuth::class)->verifyIdToken($idToken);
            $uid = $verifiedIdToken->claims()->get('sub');

            if (!$uid) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $laravelUser = User::firstOrCreate(
                ['firebase_uid' => $uid],
                [
                    'email' => $request->email,
                    'name' => $request->name,
                ]
            );

            return response()->json([
                'message' => 'ログイン成功',
                'user' => [
                    'id' => $laravelUser->id,
                    'name' => $laravelUser->name,
                    'email' => $laravelUser->email,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
