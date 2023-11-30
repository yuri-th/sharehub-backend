<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'uid' => 'required|string',
        ]);

        // Laravel ユーザーテーブルで Firebase UID に対応するユーザーを検索
        $laravelUser = User::where('firebase_uid', $request->uid)->first();

        if ($laravelUser) {
            // ユーザーが存在する場合はログイン成功として処理
            return response()->json(['message' => 'ログイン成功']);
        } else {
            return response()->json(['error' => 'ユーザーが存在しません'], 401);
        }

    }
}
