<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;

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
                ->with(['author:id,name', 'categories:id,name', 'tags:id,name'])
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
            $path = $request->file('featured_image')->store('public/posts');
            $validated['featured_image'] = Storage::url($path);
        }

        // Set published_at based on status
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        // Create post
        $post = Post::create([
            ...$validated,
            'author_id' => auth()->id(),
        ]);

        // Sync relationships
        if (isset($validated['category_ids'])) {
            $post->categories()->sync($validated['category_ids']);
        }
        if (isset($validated['tag_ids'])) {
            $post->tags()->sync($validated['tag_ids']);
        }

        // Handle post meta
        if (isset($validated['meta'])) {
            foreach ($validated['meta'] as $key => $value) {
                $post->postMeta()->create([
                    'key' => $key,
                    'value' => $value,
                ]);
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
        $post->update($validated);

        // Sync relationships
        if (isset($validated['category_ids'])) {
            $post->categories()->sync($validated['category_ids']);
        }
        if (isset($validated['tag_ids'])) {
            $post->tags()->sync($validated['tag_ids']);
        }

        // Handle post meta
        if (isset($validated['meta'])) {
            // Delete existing meta
            $post->postMeta()->delete();
            // Create new meta
            foreach ($validated['meta'] as $key => $value) {
                $post->postMeta()->create([
                    'key' => $key,
                    'value' => $value,
                ]);
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