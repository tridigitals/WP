<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        // Update tag counts
        $post->tags->each->updateCount();

        // Clear relevant caches
        $this->clearCaches();
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        // Update tag counts if tags have changed
        if ($post->wasChanged('tags')) {
            // Update old tags
            $post->getOriginal('tags', collect())->each->updateCount();
            // Update new tags
            $post->tags->each->updateCount();
        }

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        // Update tag counts
        $post->tags->each->updateCount();

        // Delete associated meta
        $post->meta()->delete();

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        // Update tag counts
        $post->tags->each->updateCount();

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        // Update tag counts
        $post->tags->each->updateCount();

        // Delete associated meta
        $post->meta()->forceDelete();

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Post "saving" event.
     */
    public function saving(Post $post): void
    {
        // Ensure published_at is set when status is published
        if ($post->status === 'published' && !$post->published_at) {
            $post->published_at = now();
        }
    }

    /**
     * Clear relevant caches.
     */
    private function clearCaches(): void
    {
        // Clear post listings cache
        Cache::tags(['posts'])->flush();

        // Clear homepage cache if exists
        Cache::forget('homepage');

        // Clear sitemap cache
        Cache::forget('sitemap');

        // Clear RSS feed cache
        Cache::forget('rss-feed');
    }
}