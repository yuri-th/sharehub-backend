<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $items = Comment::with('user')->get();
            $formattedComments = $items->map(function ($comment) {
                return [
                    'user_name' => $comment->user->name,
                    'comment' => $comment->comment,
                    'tweet_id' => $comment->tweet_id,
                ];
            });

            return response()->json([
                'data' => $formattedComments,
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'uid' => 'required|string',
                'id_token' => 'required|string',
                'tweet_id' => 'required',
                'comment' => 'required|string|max:400',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation Error: ' . json_encode($validator->errors()->toArray()));

                return response()->json(['error' => $validator->errors()], 422);
            }

            // Laravelデータベース内でFirebase UIDを使ってユーザーを取得
            $laravelUser = User::where('firebase_uid', $request->uid)->first();

            \Log::info('Debug: ' . json_encode($laravelUser));
            \Log::info('Debug: ' . json_encode($request->all()));



            $user_id = $laravelUser->id;

            $comment = Comment::create([
                'user_id' => $user_id,
                'tweet_id' => $request->tweet_id,
                'comment' => $request->input('comment'),
            ]);

            return response()->json(['data' => $comment], 201);
        } catch (\Exception $e) {
            \Log::error('CommentController@store Error: ' . $e->getMessage());
            \Log::info('Debug: Comment data - ' . json_encode($request->input('comment')));
            \Log::error($e);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
