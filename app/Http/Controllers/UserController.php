<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = User::all();
        return response()->json([
            'data' => $items
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \Log::info('Received data from Firebase:', $request->all());

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'firebase_uid' => 'required|string',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation Error: ' . json_encode($validator->errors()));
            return response()->json(['error' => $validator->errors()], 400);
        }


        try {
            // Firebaseから送信されたユーザー情報を取得
            $data = $request->all();

            // ユーザーモデルにデータを保存
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'firebase_uid' => $data['firebase_uid'],
            ]);

            return response()->json(['data' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'ユーザーの作成中にエラーが発生しました。'], 500);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */


}
