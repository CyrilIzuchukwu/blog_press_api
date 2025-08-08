<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InteractionController extends Controller
{
    public function like(Request $request, $postId): JsonResponse
    {
        try {
            $post = Post::find($postId);
            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Post not found'
                ], 404);
            }

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id'
            ]);

            $existingLike = Like::where('post_id', $post->id)
                ->where('user_id', $validated['user_id'])
                ->first();

            if ($existingLike) {
                $existingLike->delete();
                $message = 'Post unliked successfully';
                $action = 'unliked';
            } else {
                Like::create([
                    'post_id' => $post->id,
                    'user_id' => $validated['user_id']
                ]);
                $message = 'Post liked successfully';
                $action = 'liked';
            }

            $post->load(['likes.user']);

            return response()->json([
                'status' => 'success',
                'action' => $action,
                'likes_count' => $post->likes->count(),
                'data' => $post->likes,
                'message' => $message
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }



    public function comment(Request $request, $postId): JsonResponse
    {
        try {

            $post = Post::findOrFail($postId);


            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'comment' => 'required|string|max:1000'
            ]);


            $validated['post_id'] = $post->id;
            $comment = Comment::create($validated)->load('user');

            return response()->json([
                'status' => 'success',
                'data' => $comment,
                'message' => 'Comment added successfully'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
