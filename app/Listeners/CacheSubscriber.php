<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheSubscriber
{
    /**
     * Handle post events.
     */
    public function handlePostEvent($event): void
    {
        Cache::tags(['posts', 'home'])->flush();
        Cache::forget('sitemap');
        Cache::forget('post_count');
    }

    /**
     * Handle category events.
     */
    public function handleCategoryEvent($event): void
    {
        Cache::tags(['categories', 'navigation'])->flush();
        Cache::forget('category_list');
        Cache::forget('category_tree');
    }

    /**
     * Handle tag events.
     */
    public function handleTagEvent($event): void
    {
        Cache::tags(['tags'])->flush();
        Cache::forget('tag_cloud');
        Cache::forget('popular_tags');
    }

    /**
     * Handle media events.
     */
    public function handleMediaEvent($event): void
    {
        Cache::tags(['media'])->flush();
        Cache::forget('media_stats');
    }

    /**
     * Handle settings events.
     */
    public function handleSettingsEvent($event): void
    {
        Cache::tags(['settings'])->flush();
        Cache::forget('site_settings');
        Cache::forget('maintenance_mode');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events): array
    {
        return [
            'App\Events\PostPublished' => 'handlePostEvent',
            'App\Events\PostUpdated' => 'handlePostEvent',
            'App\Events\PostDeleted' => 'handlePostEvent',
            
            'App\Events\CategoryCreated' => 'handleCategoryEvent',
            'App\Events\CategoryUpdated' => 'handleCategoryEvent',
            'App\Events\CategoryDeleted' => 'handleCategoryEvent',
            
            'App\Events\TagCreated' => 'handleTagEvent',
            'App\Events\TagUpdated' => 'handleTagEvent',
            'App\Events\TagDeleted' => 'handleTagEvent',
            
            'App\Events\MediaUploaded' => 'handleMediaEvent',
            'App\Events\MediaDeleted' => 'handleMediaEvent',
            
            'App\Events\SettingsUpdated' => 'handleSettingsEvent',
        ];
    }
}