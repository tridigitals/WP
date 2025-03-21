<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('posts')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Tags/Index', [
            'tags' => $tags
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Tags/Create');
    }

    public function store(TagRequest $request)
    {
        $tag = Tag::create($request->validated());

        return redirect()
            ->route('admin.tags.edit', $tag)
            ->with('success', 'Tag berhasil dibuat.');
    }

    public function edit(Tag $tag)
    {
        return Inertia::render('Admin/Tags/Edit', [
            'tag' => $tag
        ]);
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return back()->with('success', 'Tag berhasil diperbarui.');
    }

    public function destroy(Tag $tag)
    {
        // Clean up post relationships
        $tag->posts()->detach();
        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag berhasil dihapus.');
    }

    // API endpoint for tag suggestions
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        return Tag::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);
    }
}
