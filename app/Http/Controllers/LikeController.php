<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $likes = Like::with('user')->get();
            $likeCounts = $likes->groupBy('tweet_id')->map(function ($group) {
                return [
                    'like_count' => $group->count(),
                    'users' => $group->map(function ($like) {
                        return $like->user->name;
                    })->unique()->toArray(),
                ];
            });

            return response()->json(['data' => $likeCounts], 200);
        } catch (\Exception $e) {
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
                return response()->json([
                    'error' => 'Authentication required'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'tweet_id' => 'required|integer|exists:tweets,id',
            ]);

            if ($validator->fails()) {
                \Log::error('Like validation error: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors()], 422);
            }

            $laravelUser = User::where('firebase_uid', $uid)->first();

            if (!$laravelUser) {
                \Log::error("User not found for UID: {$uid}");
                return response()->json(['error' => 'User not found'], 404);
            }

            $user_id = $laravelUser->id;

            // ツイートの存在確認
            $tweet = Tweet::find($request->tweet_id);

            if (!$tweet) {
                return response()->json(['error' => 'Tweet not found'], 404);
            }

            // いいねを作成または更新
            $like = Like::updateOrCreate(
                ['user_id' => $user_id, 'tweet_id' => $request->tweet_id],
                ['user_id' => $user_id, 'tweet_id' => $request->tweet_id]
            );

            return response()->json(['data' => $like], 201);

        } catch (\Exception $e) {
            \Log::error('Like creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function show($id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $uid = $request->header('X-User-UID');

            if (!$uid) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $user = User::where('firebase_uid', $uid)->first();

            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $like = Like::where('tweet_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$like) {
                return response()->json(['error' => 'Like not found'], 404);
            }

            $like->delete();

            return response()->json(['message' => 'Like deleted successfully'], 200);

        } catch (\Exception $e) {
            \Log::error('Like deletion error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
