<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PostRequest;
use App\Models\Post;
use App\Models\Meta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()
            ->with(['category', 'tags', 'meta', 'user'])
            ->when($request->search, function (Builder $query, string $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->status, function (Builder $query, string $status) {
                $query->where('status', $status);
            })
            ->when($request->post_type, function (Builder $query, string $type) {
                $query->where('post_type', $type);
            })
            ->when($request->category_id, function (Builder $query, int $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->tag_id, function (Builder $query, int $tagId) {
                $query->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('tags.id', $tagId);
                });
            });

        $posts = $query->latest()
            ->paginate($request->input('per_page', 15));

        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $post = Post::create($request->validated());

            // Sync tags if provided
            if ($request->has('tags')) {
                $post->tags()->sync($request->input('tags'));
            }

            // Create meta information if provided
            if ($request->has('meta')) {
                $post->meta()->create($request->input('meta'));
            }

            DB::commit();

            $post->load(['category', 'tags', 'meta', 'user']);

            return response()->json([
                'message' => 'Post created successfully',
                'post' => $post
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): JsonResponse
    {
        $post->load(['category', 'tags', 'meta', 'user']);

        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostRequest $request, Post $post): JsonResponse
    {
        try {
            DB::beginTransaction();

            $post->update($request->validated());

            // Sync tags if provided
            if ($request->has('tags')) {
                $post->tags()->sync($request->input('tags'));
            }

            // Update meta information if provided
            if ($request->has('meta')) {
                $post->meta()->updateOrCreate(
                    ['metable_id' => $post->id, 'metable_type' => Post::class],
                    $request->input('meta')
                );
            }

            DB::commit();

            $post->load(['category', 'tags', 'meta', 'user']);

            return response()->json([
                'message' => 'Post updated successfully',
                'post' => $post
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Delete related meta
            $post->meta()->delete();
            
            // Delete the post (soft delete)
            $post->delete();

            DB::commit();

            return response()->json([
                'message' => 'Post deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update post status.
     */
    public function updateStatus(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:draft,published,scheduled'],
            'published_at' => ['required_if:status,scheduled', 'nullable', 'date', 'after:now']
        ]);

        try {
            $post->update([
                'status' => $request->status,
                'published_at' => $request->published_at
            ]);

            return response()->json([
                'message' => 'Post status updated successfully',
                'post' => $post
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating post status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle post visibility.
     */
    public function toggleVisibility(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'visibility' => ['required', 'string', 'in:public,private,password_protected'],
            'password' => ['required_if:visibility,password_protected', 'nullable', 'string', 'min:6']
        ]);

        try {
            $post->update([
                'visibility' => $request->visibility,
                'password' => $request->password
            ]);

            return response()->json([
                'message' => 'Post visibility updated successfully',
                'post' => $post
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating post visibility',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
