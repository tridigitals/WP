<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query()
            ->with(['meta'])
            ->when($request->search, function (Builder $query, string $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->popular, function (Builder $query) {
                $query->where('count', '>', 0)->orderBy('count', 'desc');
            })
            ->when($request->sort, function (Builder $query, string $sort) {
                [$field, $direction] = explode(',', $sort);
                $query->orderBy($field, $direction ?? 'asc');
            }, function (Builder $query) {
                $query->orderBy('name');
            });

        $perPage = $request->input('per_page', 15);
        $tags = $perPage === 'all' ? $query->get() : $query->paginate($perPage);

        return response()->json($tags);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TagRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $tag = Tag::create($request->validated());

            // Create meta information if provided
            if ($request->has('meta')) {
                $tag->meta()->create($request->input('meta'));
            }

            DB::commit();

            $tag->load(['meta']);

            return response()->json([
                'message' => 'Tag created successfully',
                'tag' => $tag
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag): JsonResponse
    {
        $tag->load(['meta', 'posts' => function ($query) {
            $query->latest()->take(10);
        }]);

        return response()->json([
            'tag' => $tag,
            'post_count' => $tag->count,
            'latest_posts' => $tag->posts
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TagRequest $request, Tag $tag): JsonResponse
    {
        try {
            DB::beginTransaction();

            $tag->update($request->validated());

            // Update meta information if provided
            if ($request->has('meta')) {
                $tag->meta()->updateOrCreate(
                    ['metable_id' => $tag->id, 'metable_type' => Tag::class],
                    $request->input('meta')
                );
            }

            DB::commit();

            $tag->load(['meta']);

            return response()->json([
                'message' => 'Tag updated successfully',
                'tag' => $tag
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Detach all posts
            $tag->posts()->detach();
            
            // Delete meta
            $tag->meta()->delete();
            
            // Delete the tag
            $tag->delete();

            DB::commit();

            return response()->json([
                'message' => 'Tag deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting tag',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Merge tags into a single tag.
     */
    public function merge(Request $request): JsonResponse
    {
        $request->validate([
            'source_tags' => ['required', 'array', 'min:2'],
            'source_tags.*' => ['exists:tags,id'],
            'target_tag_id' => ['required', 'exists:tags,id', 'not_in:' . implode(',', $request->source_tags)]
        ]);

        try {
            DB::beginTransaction();

            $targetTag = Tag::findOrFail($request->target_tag_id);
            $sourceTags = Tag::whereIn('id', $request->source_tags)
                ->where('id', '!=', $targetTag->id)
                ->get();

            foreach ($sourceTags as $sourceTag) {
                // Move all posts to target tag
                $sourceTag->posts()->each(function ($post) use ($targetTag) {
                    if (!$post->tags()->where('tags.id', $targetTag->id)->exists()) {
                        $post->tags()->attach($targetTag->id);
                    }
                });

                // Delete source tag
                $sourceTag->delete();
            }

            // Update target tag count
            $targetTag->updateCount();

            DB::commit();

            return response()->json([
                'message' => 'Tags merged successfully',
                'tag' => $targetTag->load('meta')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error merging tags',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update tags.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'tags' => ['required', 'array'],
            'tags.*.id' => ['required', 'exists:tags,id'],
            'tags.*.name' => ['required', 'string', 'max:255'],
            'tags.*.slug' => ['required', 'string', 'max:255']
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->tags as $tagData) {
                $tag = Tag::find($tagData['id']);
                $tag->update($tagData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Tags updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating tags',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
