<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $tweetId = $request->query('tweet_id');

            // tweet_idがある場合は絞り込み、ない場合は全件取得
            if ($tweetId) {
                $items = Comment::with('user')
                    ->where('tweet_id', $tweetId)
                    ->get();
            } else {
                $items = Comment::with('user')->get();
            }

            $formattedComments = $items->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'user_name' => $comment->user->name,
                    'comment' => $comment->comment,
                    'tweet_id' => $comment->tweet_id,
                ];
            });

            return response()->json(['data' => $formattedComments], 200);

        } catch (\Exception $e) {
            \Log::error('Comment index error: ' . $e->getMessage());
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
            $uid = $request->firebase_uid;

            if (!$uid) {
                return response()->json(['error' => 'Authentication required'], 401);
            }

            $validator = Validator::make($request->all(), [
                'tweet_id' => 'required|integer|exists:tweets,id',
                'comment' => 'required|string|max:400',
            ]);

            if ($validator->fails()) {
                \Log::error('Comment validation error: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors()], 422);
            }

            $laravelUser = User::where('firebase_uid', $uid)->first();

            if (!$laravelUser) {
                \Log::error("User not found for UID: {$uid}");
                return response()->json(['error' => 'User not found'], 404);
            }

            $comment = Comment::create([
                'user_id' => $laravelUser->id,
                'tweet_id' => $request->tweet_id,
                'comment' => $request->comment,
            ]);

            return response()->json(['data' => $comment], 201);

        } catch (\Exception $e) {
            \Log::error('Comment creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    // public function show(Comment $comment)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, Comment $comment)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */

    // public function destroy(Request $request, $id)
    // {
    // }

}
