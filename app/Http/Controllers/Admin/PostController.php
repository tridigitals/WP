<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with(['author', 'category', 'tags'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Posts/Create', [
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function store(PostRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('posts/images', 'public');
            $data['featured_image'] = Storage::url($path);
        }

        $data['author_id'] = auth()->id();
        
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = Post::create($data);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Post berhasil dibuat.');
    }

    public function edit(Post $post)
    {
        return Inertia::render('Admin/Posts/Edit', [
            'post' => $post->load(['author', 'category', 'tags']),
            'categories' => Category::orderBy('name')->get()
        ]);
    }

    public function update(PostRequest $request, Post $post)
    {
        $data = $request->validated();

        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($post->featured_image) {
                $oldPath = str_replace('/storage/', '', $post->featured_image);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('featured_image')->store('posts/images', 'public');
            $data['featured_image'] = Storage::url($path);
        }

        if ($data['status'] === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);
        
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return back()->with('success', 'Post berhasil diperbarui.');
    }

    public function destroy(Post $post)
    {
        if ($post->featured_image) {
            $path = str_replace('/storage/', '', $post->featured_image);
            Storage::disk('public')->delete($path);
        }

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post berhasil dihapus.');
    }

    public function toggleStatus(Post $post)
    {
        $newStatus = $post->status === 'published' ? 'draft' : 'published';
        $post->update([
            'status' => $newStatus,
            'published_at' => $newStatus === 'published' ? now() : null
        ]);

        return back()->with('success', 'Status post berhasil diperbarui.');
    }
}
