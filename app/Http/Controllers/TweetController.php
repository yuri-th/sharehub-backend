<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Tweet;
use App\Models\User;
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
                    'user_name' => $tweet->user->name,
                    'tweet_text' => $tweet->tweet_text,
                    'tweet_id' => $tweet->id,
                ];
            });

            return response()->json([
                'data' => $formattedTweets,
            ], 200);
        } catch (\Exception $e) {
            // 例外が発生した場合、ログにエラーを記録する
            \Log::error($e);

            return response()->json([
                'error' => 'Internal Server Error',
            ], 500);
        }
    }

    /**
     * Firebase ID トークンを検証してユーザー情報を取得
     *
     * @param string $idToken
     * @return mixed|null
     */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tweet_text' => 'required|string|max:400',
                'uid' => 'required|string',
                'id_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation Error: ' . json_encode($validator->errors()->toArray()));

                return response()->json(['error' => $validator->errors()], 422);
            }

            // Laravelデータベース内でFirebase UIDを使ってユーザーを取得
            $laravelUser = User::where('firebase_uid', $request->uid)->first();

            \Log::info('Debug: ' . json_encode($laravelUser));

            $user_id = $laravelUser->id;

            $tweet = Tweet::create([
                'user_id' => $user_id,
                'tweet_text' => $request->input('tweet_text'),
            ]);

            return response()->json(['data' => $tweet], 201);
        } catch (\Exception $e) {
            \Log::error('TweetController@store Error: ' . $e->getMessage());
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
    public function show($id)
    {
        // $data = Tweet::select('id')->find($id);
        // return response()->json(['data' => $data]);
        // ツイートを取得
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        \Log::info('Tweet ID: ' . $id);
        \Log::error('Tweet not found for ID: ' . $id);

        $userUid = $request->header('X-User-UID');

        $user = User::where('firebase_uid', $userUid)->first();

        if ($user) {
            $tweet = Tweet::find($id);

            if (!$tweet) {
                return response()->json(['error' => 'Tweet not found'], 404);
            }

            if ($tweet->user_id === $user->id) {
                $tweet->delete();
                return response()->json(['message' => 'Tweet deleted successfully']);
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    }
}
