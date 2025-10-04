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
        try {
            $items = User::all();
            return response()->json(['data' => $items], 200);
        } catch (\Exception $e) {
            \Log::error('User index error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $uid = $request->header('X-User-UID');

            if (!$uid) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $user = User::firstOrCreate(
                ['firebase_uid' => $uid],
                [
                    'name' => $request->name,
                    'email' => $request->email,
                ]
            );

            return response()->json(['data' => $user], 201);

        } catch (\Exception $e) {
            \Log::error('User creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
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
