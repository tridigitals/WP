<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryObserver
{
    /**
     * Handle the Category "creating" event.
     */
    public function creating(Category $category): void
    {
        // Generate slug if not provided
        if (!$category->slug) {
            $category->slug = $this->generateUniqueSlug($category);
        }
    }

    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Category "updating" event.
     */
    public function updating(Category $category): void
    {
        // Generate new slug if name changed and slug not manually set
        if ($category->isDirty('name') && !$category->isDirty('slug')) {
            $category->slug = $this->generateUniqueSlug($category);
        }

        // Prevent circular references in parent-child relationship
        if ($category->isDirty('parent_id') && $category->parent_id) {
            $parent = Category::find($category->parent_id);
            if ($parent && ($parent->id === $category->id || $parent->isDescendantOf($category))) {
                throw new \Exception('Cannot create circular reference in category hierarchy');
            }
        }
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        // Update child categories if parent changed
        if ($category->wasChanged('parent_id')) {
            // Update the order of siblings
            Category::where('parent_id', $category->getOriginal('parent_id'))
                ->where('order', '>', $category->getOriginal('order'))
                ->decrement('order');

            Category::where('parent_id', $category->parent_id)
                ->where('order', '>=', $category->order)
                ->increment('order');
        }

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Category "deleting" event.
     */
    public function deleting(Category $category): void
    {
        // Move children to parent category
        if ($category->children()->exists()) {
            $category->children()->update(['parent_id' => $category->parent_id]);
        }

        // Update posts to remove category
        $category->posts()->update(['category_id' => null]);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        // Delete associated meta
        $category->meta()->delete();

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        // Delete associated meta
        $category->meta()->forceDelete();

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Generate a unique slug for the category.
     */
    private function generateUniqueSlug(Category $category): string
    {
        $slug = Str::slug($category->name);
        $count = 1;

        while (
            Category::where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()
        ) {
            $slug = Str::slug($category->name) . '-' . $count++;
        }

        return $slug;
    }

    /**
     * Clear relevant caches.
     */
    private function clearCaches(): void
    {
        // Clear category cache
        Cache::tags(['categories'])->flush();

        // Clear menu cache if exists
        Cache::forget('navigation_menu');

        // Clear sitemap cache
        Cache::forget('sitemap');
    }
}