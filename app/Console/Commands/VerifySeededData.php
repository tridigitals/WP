<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Setting;
use Illuminate\Console\Command;

class VerifySeededData extends Command
{
    protected $signature = 'verify:seeded-data';
    protected $description = 'Verify seeded data in the database';

    public function handle()
    {
        $this->info('Verifying seeded data...');

        // Check users
        $this->info("\nUsers:");
        $users = User::all();
        $this->line("Total users: " . $users->count());
        $this->line("Admin user: " . User::where('role', 'admin')->first()->email);

        // Check categories
        $this->info("\nCategories:");
        $categories = Category::all();
        $this->line("Total categories: " . $categories->count());
        $this->line("Main categories: " . Category::whereNull('parent_id')->count());
        $this->line("Subcategories: " . Category::whereNotNull('parent_id')->count());

        // Check tags
        $this->info("\nTags:");
        $this->line("Total tags: " . Tag::count());
        $this->line("Sample tags: " . Tag::take(5)->pluck('name')->join(', '));

        // Check posts
        $this->info("\nPosts:");
        $totalPosts = Post::count();
        $publishedPosts = Post::where('status', 'published')->count();
        $this->line("Total posts: $totalPosts");
        $this->line("Published posts: $publishedPosts");
        
        // Sample post details
        $post = Post::with(['author', 'categories', 'tags'])->first();
        $this->info("\nSample post details:");
        $this->line("Title: " . $post->title);
        $this->line("Author: " . $post->author->name);
        $this->line("Categories: " . $post->categories->pluck('name')->join(', '));
        $this->line("Tags: " . $post->tags->pluck('name')->join(', '));

        // Check settings
        $this->info("\nSettings:");
        $this->line("Total settings: " . Setting::count());
        $this->line("Groups: " . Setting::distinct('group')->pluck('group')->join(', '));
    }
}