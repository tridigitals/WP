<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use App\Observers\PostObserver;
use App\Observers\CategoryObserver;
use App\Observers\TagObserver;
use App\Observers\MediaObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // CMS Events
        'App\Events\PostPublished' => [
            'App\Listeners\UpdateSitemap',
            'App\Listeners\NotifySubscribers',
            'App\Listeners\GenerateSocialPreviews',
        ],

        'App\Events\MediaUploaded' => [
            'App\Listeners\OptimizeMedia',
            'App\Listeners\GenerateThumbnails',
        ],

        'App\Events\CategoryCreated' => [
            'App\Listeners\UpdateNavigationCache',
        ],

        'App\Events\TagCreated' => [
            'App\Listeners\UpdateTagCloud',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register model observers
        Post::observe(PostObserver::class);
        Category::observe(CategoryObserver::class);
        Tag::observe(TagObserver::class);
        Media::observe(MediaObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\CacheSubscriber',
        'App\Listeners\SecuritySubscriber',
        'App\Listeners\ActivityLogSubscriber',
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array<int, string>
     */
    protected function discoverEventsWithin(): array
    {
        return [
            $this->app->path('Listeners'),
        ];
    }
}