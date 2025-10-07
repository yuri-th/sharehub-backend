<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Tweet;
use App\Models\User;
use App\Models\like;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Http\Resources\TweetResource;


class TweetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $items = Tweet::with('user')->get();
            $formattedTweets = $items->map(function ($tweet) {
                return [
                    'user_name' => $tweet->user ? $tweet->user->name : 'Unknown User',
                    'tweet_text' => $tweet->tweet_text,
                    'tweet_id' => $tweet->id,
                ];
            });

            return response()->json([
                'data' => $formattedTweets,
            ], 200);
        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'error' => 'Internal Server Error',
            ], 500);
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
            $uid = $request->firebase_uid;

            if (!$uid) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $validator = Validator::make($request->all(), [
                'tweet_text' => 'required|string|max:400',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation Error: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors()], 422);
            }

            $laravelUser = User::where('firebase_uid', $uid)->first();

            if (!$laravelUser) {
                \Log::error("User not found for UID: {$uid}");
                return response()->json(['error' => 'User not found'], 404);
            }

            $tweet = Tweet::create([
                'user_id' => $laravelUser->id,
                'tweet_text' => $request->input('tweet_text'),
            ]);

            return response()->json(['data' => $tweet], 201);

        } catch (\Exception $e) {
            \Log::error('Tweet creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tweet = Tweet::with('user')->find($id);

        if (!$tweet) {
            return response()->json(['error' => 'Tweet not found'], 404);
        }

        return new TweetResource($tweet);
    }

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
     *
     * ツイートと一緒に、関連するいいねとコメントを削除
     */
    public function destroy(Request $request, $id)
    {
        $uid = $request->firebase_uid;

        if (!$uid) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $user = User::where('firebase_uid', $uid)->first();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $tweet = Tweet::find($id);

        if (!$tweet) {
            return response()->json(['error' => 'Tweet not found'], 404);
        }

        if ($tweet->user_id !== $user->id) {
            return response()->json(['error' => '投稿者以外削除できません'], 403);
        }

        Like::where('tweet_id', $id)->delete();
        Comment::where('tweet_id', $id)->delete();
        $tweet->delete();

        return response()->json(['message' => 'Tweet deleted successfully']);
    }
}
