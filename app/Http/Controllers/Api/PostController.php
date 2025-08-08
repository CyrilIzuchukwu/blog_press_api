<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id): JsonResponse
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return response()->json([
                'status' => 'error',
                'message' => "Blog with ID {$id} not found"
            ], 404);
        }

        $posts = $blog->posts()->with(['user', 'likes', 'comments.user'])->get();

        if ($posts->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => [],
                'message' => 'No posts found for this blog'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $posts,
            'message' => 'Posts retrieved successfully'
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Blog $blog): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image_url' => 'nullable|url',
                'user_id' => 'required|exists:users,id'
            ]);

            $validated['blog_id'] = $blog->id;
            $post = Post::create($validated);
            $post->load(['user', 'blog', 'likes', 'comments.user']);

            return response()->json([
                'status' => 'success',
                'data' => $post,
                'message' => 'Post created successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog, Post $post): JsonResponse
    {
        if ($post->blog_id !== $blog->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found in this blog'
            ], 404);
        }

        $post->load(['user', 'blog', 'likes.user', 'comments.user']);

        return response()->json([
            'status' => 'success',
            'data' => $post,
            'message' => 'Post retrieved successfully'
        ]);
    }





    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog, Post $post): JsonResponse
    {
        if ($post->blog_id !== $blog->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found in this blog'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'image_url' => 'nullable|url',
            ]);

            $post->update($validated);
            $post->load(['user', 'blog', 'likes', 'comments.user']);

            return response()->json([
                'status' => 'success',
                'data' => $post,
                'message' => 'Post updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($blogId, $postId): JsonResponse
    {
        $blog = Blog::find($blogId);
        if (!$blog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Blog not found'
            ], 404);
        }

        $post = Post::where('id', $postId)->where('blog_id', $blogId)->first();

        if (!$post) {
            return response()->json([
                'status' => 'error',
                'message' => 'Post not found in this blog'
            ], 404);
        }

        $post->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Post deleted successfully'
        ]);
    }
}
