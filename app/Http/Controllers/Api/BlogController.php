<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $blogs = Blog::with(['user', 'posts'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $blogs,
            'message' => 'Blogs retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image_url' => 'nullable|url',
                'user_id' => 'required|exists:users,id'
            ]);


            if (Blog::where('user_id', $validated['user_id'])
                ->where('title', $validated['title'])
                ->exists()
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Blog creation failed',
                    'errors' => ['title' => ['You already have a blog with this title. Please choose a different title.']]
                ], 422);
            }

            $blog = Blog::create($validated);
            $blog->load(['user', 'posts']);

            return response()->json([
                'status' => 'success',
                'data' => $blog,
                'message' => 'Blog created successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog): JsonResponse
    {
        $blog->load(['user', 'posts.likes', 'posts.comments.user']);

        if (!$blog) {
            return response()->json([
                'message' => 'Blog not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $blog,
            'message' => 'Blog retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {

            $blog = Blog::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'image_url' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $blog->update($validator->validated());
            $blog->load(['user', 'posts']);

            return response()->json([
                'status' => 'success',
                'message' => "Blog with ID {$id} updated successfully",
                'data' => $blog
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Blog with ID {$id} not found",
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(?Blog $blog): JsonResponse
    {

        if (!$blog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Blog not found'
            ], 404);
        }

        $id = $blog->id;
        $blog->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Blog with ID {$id} deleted successfully"
        ]);
    }
}
