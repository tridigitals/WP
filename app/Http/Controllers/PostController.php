<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $direction = $request->input('direction', 'desc');
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $sort = $request->input('sort');
        $validColumns = ['title', 'status', 'created_at', 'published_at'];
        if (!in_array($sort, $validColumns)) {
            $sort = 'created_at';
        }

        $status = $request->input('status');
        $validStatuses = ['all', 'draft', 'published', 'scheduled', 'trash'];
        if (!in_array($status, $validStatuses)) {
            $status = 'all';
        }

        return Inertia::render('Posts/Index', [
            'posts' => Post::query()
                ->select('id', 'title', 'excerpt', 'status', 'author_id', 'published_at', 'created_at', 'deleted_at')
                ->with(['author:id,name', 'categories:id,name', 'tags:id,name', 'postMeta'])
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('title', 'like', "%{$search}%")
                          ->orWhere('content', 'like', "%{$search}%");
                })
                ->when($status !== 'all', function ($query) use ($status) {
                    if ($status === 'trash') {
                        $query->onlyTrashed();
                    } else {
                        $query->status($status);
                    }
                })
                ->orderBy($sort, $direction)
                ->paginate($request->input('per_page', 10))
                ->withQueryString(),
            'filters' => [
                'search' => $request->input('search'),
                'sort' => $sort,
                'direction' => $direction,
                'status' => $status,
                'per_page' => $request->input('per_page', 10),
            ],
            'categories' => Category::select('id', 'name')->get(),
            'tags' => Tag::select('id', 'name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Posts/Create', [
            'categories' => Category::select('id', 'name')->get(),
            'tags' => Tag::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('Post meta data received:', ['meta' => $request->input('meta')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|max:2048',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
            'published_at' => 'required_if:status,scheduled|nullable|date',
            'meta' => 'array',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            
            // Store original file using public disk
            $path = Storage::disk('public')->putFileAs('posts', $file, $fileName);
            $validated['featured_image'] = Storage::disk('public')->url($path);
            
            // Generate and store thumbnail
            $image = Image::make($file);
            $image->fit(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Create thumbnails directory if it doesn't exist
            $thumbnailPath = 'posts/thumbnails';
            if (!Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->makeDirectory($thumbnailPath);
            }
            
            // Save thumbnail
            $thumbnailName = 'thumb_' . $fileName;
            $thumbnailFullPath = $thumbnailPath . '/' . $thumbnailName;
            Storage::disk('public')->put($thumbnailFullPath, (string) $image->encode());
        }

        // Set published_at based on status
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        // Set author_id
        $validated['author_id'] = auth()->id();

        // Create post
        $post = Post::create(Arr::except($validated, ['meta', 'category_ids', 'tag_ids']));

        // Sync relationships
        if (isset($validated['category_ids'])) {
            $post->categories()->sync($validated['category_ids']);
        }
        if (isset($validated['tag_ids'])) {
            $post->tags()->sync($validated['tag_ids']);
        }

        // Handle post meta
        if (isset($validated['meta']) && is_array($validated['meta'])) {
            Log::info('Processing meta data for post:', ['post_id' => $post->id, 'meta' => $validated['meta']]);
            
            foreach ($validated['meta'] as $key => $value) {
                if (!empty($value)) {
                    try {
                        $post->postMeta()->create([
                            'meta_key' => $key,
                            'meta_value' => $value,
                        ]);
                        Log::info('Meta created:', ['key' => $key, 'value' => $value]);
                    } catch (\Exception $e) {
                        Log::error('Error saving meta:', [
                            'post_id' => $post->id,
                            'key' => $key,
                            'value' => $value,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post): Response
    {
        $post->load(['author:id,name', 'categories:id,name', 'tags:id,name', 'postMeta']);
        
        return Inertia::render('Posts/Show', [
            'post' => $post,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post): Response
    {
        $post->load(['categories:id,name', 'tags:id,name', 'postMeta']);
        
        return Inertia::render('Posts/Edit', [
            'post' => $post,
            'categories' => Category::select('id', 'name')->get(),
            'tags' => Tag::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        Log::info('Post meta data update received:', ['meta' => $request->input('meta')]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|image|max:2048',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
            'published_at' => 'required_if:status,scheduled|nullable|date',
            'meta' => 'array',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($post->featured_image) {
                Storage::delete(str_replace('/storage', 'public', $post->featured_image));
            }
            $path = $request->file('featured_image')->store('public/posts');
            $validated['featured_image'] = Storage::url($path);
        }

        // Update published_at based on status changes
        if ($validated['status'] === 'published' && !$post->isPublished()) {
            $validated['published_at'] = now();
        }

        // Update post
        $post->update(Arr::except($validated, ['meta', 'category_ids', 'tag_ids']));

        // Sync relationships
        if (isset($validated['category_ids'])) {
            $post->categories()->sync($validated['category_ids']);
        }
        if (isset($validated['tag_ids'])) {
            $post->tags()->sync($validated['tag_ids']);
        }

        // Handle post meta
        if (isset($validated['meta']) && is_array($validated['meta'])) {
            Log::info('Processing meta update for post:', ['post_id' => $post->id, 'meta' => $validated['meta']]);
            
            // Delete existing meta
            $post->postMeta()->delete();
            
            // Create new meta
            foreach ($validated['meta'] as $key => $value) {
                if (!empty($value)) {
                    try {
                        $post->postMeta()->create([
                            'meta_key' => $key,
                            'meta_value' => $value,
                        ]);
                        Log::info('Meta updated:', ['key' => $key, 'value' => $value]);
                    } catch (\Exception $e) {
                        Log::error('Error updating meta:', [
                            'post_id' => $post->id,
                            'key' => $key,
                            'value' => $value,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post moved to trash.');
    }

    /**
     * Force delete the specified resource from storage.
     */
    public function forceDelete(int $id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        
        // Delete featured image
        if ($post->featured_image) {
            Storage::delete(str_replace('/storage', 'public', $post->featured_image));
        }

        // Delete relationships and meta
        $post->categories()->detach();
        $post->tags()->detach();
        $post->postMeta()->delete();
        
        $post->forceDelete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post permanently deleted.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(int $id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->restore();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post restored from trash.');
    }
}