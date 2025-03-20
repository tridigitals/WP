<?php

namespace App\Observers;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TagObserver
{
    /**
     * Handle the Tag "creating" event.
     */
    public function creating(Tag $tag): void
    {
        // Generate slug if not provided
        if (!$tag->slug) {
            $tag->slug = $this->generateUniqueSlug($tag);
        }

        // Initialize count
        if (!isset($tag->count)) {
            $tag->count = 0;
        }
    }

    /**
     * Handle the Tag "created" event.
     */
    public function created(Tag $tag): void
    {
        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Tag "updating" event.
     */
    public function updating(Tag $tag): void
    {
        // Generate new slug if name changed and slug not manually set
        if ($tag->isDirty('name') && !$tag->isDirty('slug')) {
            $tag->slug = $this->generateUniqueSlug($tag);
        }
    }

    /**
     * Handle the Tag "updated" event.
     */
    public function updated(Tag $tag): void
    {
        // Update count if it was manually changed
        if ($tag->wasChanged('count')) {
            Cache::tags(['tags'])->forget("tag_count_{$tag->id}");
        }

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Tag "deleting" event.
     */
    public function deleting(Tag $tag): void
    {
        // Detach all posts
        $tag->posts()->detach();
    }

    /**
     * Handle the Tag "deleted" event.
     */
    public function deleted(Tag $tag): void
    {
        // Delete associated meta
        $tag->meta()->delete();

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Tag "restored" event.
     */
    public function restored(Tag $tag): void
    {
        // Update post count
        $tag->updateCount();

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Tag "force deleted" event.
     */
    public function forceDeleted(Tag $tag): void
    {
        // Delete associated meta
        $tag->meta()->forceDelete();

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Generate a unique slug for the tag.
     */
    private function generateUniqueSlug(Tag $tag): string
    {
        $slug = Str::slug($tag->name);
        $count = 1;

        while (
            Tag::where('slug', $slug)
                ->where('id', '!=', $tag->id)
                ->exists()
        ) {
            $slug = Str::slug($tag->name) . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Clear relevant caches.
     */
    private function clearCaches(): void
    {
        // Clear tags cache
        Cache::tags(['tags'])->flush();

        // Clear tag cloud cache
        Cache::forget('tag_cloud');

        // Clear related tags cache
        Cache::tags(['related_tags'])->flush();

        // Clear sitemap cache
        Cache::forget('sitemap');
    }
}