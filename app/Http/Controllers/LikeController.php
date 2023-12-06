<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Like;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;

class LikeController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        try {
            // すべてのいいねの情報を取得
            $likes = Like::with('user')->get();

            // ツイートごとのいいね数といいねをしたユーザー情報を格納する連想配列
            $likeCounts = $likes->groupBy('tweet_id')->map(function ($group) {
                return [
                    'like_count' => $group->count(),
                    'users' => $group->map(function ($like) {
                        return $like->user->name;
                    })->unique()->toArray(),
                ];
            });

            // デバッグ情報をログに書き込む
            \Log::info('LikeController@index Debug: '.json_encode($likeCounts));

            return response()->json(['data' => $likeCounts], 200);
        } catch (\Exception $e) {
            \Log::error('LikeController@index Error: '.$e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'tweet_id' => 'required',
                'uid' => 'required|string',
                'id_token' => 'required|string',
            ]);

            if($validator->fails()) {
                \Log::error('Validation Error: '.json_encode($validator->errors()->toArray()));

                return response()->json(['error' => $validator->errors()], 422);
            }

            // Laravelデータベース内でFirebase UIDを使ってユーザーを取得
            $laravelUser = User::where('firebase_uid', $request->uid)->first();
            \Log::info('Frontend User Display Name: '.$request->header('X-User-Display-Name'));

            \Log::info('Debug: '.json_encode($laravelUser));

            if(!$laravelUser) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user_id = $laravelUser->id;
            $tweet = Tweet::where('id', $request->tweet_id)->first();

            if($tweet) {
                Like::updateOrCreate(
                    ['user_id' => $user_id, 'tweet_id' => $request->tweet_id],
                    ['user_id' => $user_id, 'tweet_id' => $request->tweet_id]
                );
                return response()->json(['data' => $tweet], 201);
            }

        } catch (\Exception $e) {
            \Log::error('TweetController@store Error: '.$e->getMessage());
            \Log::error($e);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id) {

        $userUid = $request->header('X-User-UID');
        $user = User::where('firebase_uid', $userUid)->first();

        if($user) {
            $like = Like::where('tweet_id', $id)->where('user_id', $user->id)->first();

            if(!$like) {
                return response()->json(['error' => 'Tweet not found'], 404);
            }

            if($like->user_id === $user->id) {
                $like->delete();
                return response()->json(['message' => 'Tweet deleted successfully']);
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    }
}
