<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\{Post, Category, Tag, Media, Meta};
use App\Observers\{
    PostObserver,
    CategoryObserver,
    TagObserver,
    MediaObserver
};

class CmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register CMS Config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/cms.php', 'cms'
        );

        // Register Singleton Services
        $this->app->singleton('cms.meta', function ($app) {
            return new \App\Services\MetaService();
        });

        $this->app->singleton('cms.media', function ($app) {
            return new \App\Services\MediaService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Model Observers
        Post::observe(PostObserver::class);
        Category::observe(CategoryObserver::class);
        Tag::observe(TagObserver::class);
        Media::observe(MediaObserver::class);

        // Configure Storage Disks
        $this->configureCmsStorage();

        // Register Custom Validation Rules
        $this->registerCustomValidationRules();

        // Configure Cache
        $this->configureCmsCache();

        // Publish Configuration
        $this->publishes([
            __DIR__.'/../../config/cms.php' => config_path('cms.php'),
        ], 'cms-config');

        // Publish Assets
        $this->publishes([
            __DIR__.'/../../resources/js' => public_path('vendor/cms/js'),
            __DIR__.'/../../resources/css' => public_path('vendor/cms/css'),
        ], 'cms-assets');

        // Register Blade Components
        $this->loadViewComponentsAs('cms', [
            'meta-tags' => \App\View\Components\MetaTags::class,
            'media-gallery' => \App\View\Components\MediaGallery::class,
        ]);

        // Register View Composers
        view()->composer('*', function ($view) {
            $view->with('cmsConfig', config('cms'));
        });
    }

    /**
     * Configure CMS storage disks.
     */
    private function configureCmsStorage(): void
    {
        // Configure Media Storage
        Storage::extend('media', function ($app, $config) {
            $driver = $config['driver'] ?? 'local';
            $config['root'] = storage_path('app/public/media');
            $config['url'] = config('app.url').'/storage/media';

            return Storage::createDriver($driver);
        });

        // Configure Cache Storage
        Storage::extend('cms-cache', function ($app, $config) {
            return Storage::createDriver([
                'driver' => 'local',
                'root' => storage_path('app/cms/cache'),
            ]);
        });
    }

    /**
     * Register custom validation rules.
     */
    private function registerCustomValidationRules(): void
    {
        Validator::extend('meta_title', function ($attribute, $value, $parameters, $validator) {
            return strlen($value) <= 60;
        }, 'The meta title must not exceed 60 characters.');

        Validator::extend('meta_description', function ($attribute, $value, $parameters, $validator) {
            return strlen($value) <= 160;
        }, 'The meta description must not exceed 160 characters.');

        Validator::extend('slug', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value);
        }, 'The slug format is invalid.');
    }

    /**
     * Configure CMS cache settings.
     */
    private function configureCmsCache(): void
    {
        // Configure Cache Store
        Cache::extend('cms', function ($app, $config) {
            return Cache::repository(
                new \Illuminate\Cache\Repository(
                    new \Illuminate\Cache\FileStore(
                        $app['files'],
                        storage_path('app/cms/cache')
                    )
                )
            );
        });

        // Set Cache Prefix
        config(['cache.prefix' => 'cms']);

        // Configure Cache TTL
        config([
            'cache.ttl' => [
                'posts' => 3600, // 1 hour
                'categories' => 7200, // 2 hours
                'tags' => 7200, // 2 hours
                'media' => 86400, // 24 hours
                'meta' => 3600, // 1 hour
            ]
        ]);
    }
}
