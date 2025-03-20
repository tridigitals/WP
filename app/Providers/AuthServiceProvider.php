<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Define policies here
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register default admin gate
        Gate::define('admin', function (User $user) {
            return $user->is_admin;
        });

        // Content management gates
        Gate::define('manage-posts', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('manage-categories', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('manage-tags', function (User $user) {
            return $user->is_admin;
        });

        // Media management gates
        Gate::define('manage-media', function (User $user) {
            return $user->is_admin;
        });

        // Settings management gates
        Gate::define('manage-settings', function (User $user) {
            return $user->is_admin;
        });

        // Post-specific gates
        Gate::define('create-post', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('edit-post', function (User $user, Post $post) {
            return $user->is_admin || $post->user_id === $user->id;
        });

        Gate::define('delete-post', function (User $user, Post $post) {
            return $user->is_admin || $post->user_id === $user->id;
        });

        Gate::define('publish-post', function (User $user) {
            return $user->is_admin;
        });

        // Category-specific gates
        Gate::define('create-category', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('edit-category', function (User $user, Category $category) {
            return $user->is_admin;
        });

        Gate::define('delete-category', function (User $user, Category $category) {
            return $user->is_admin;
        });

        // Tag-specific gates
        Gate::define('create-tag', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('edit-tag', function (User $user, Tag $tag) {
            return $user->is_admin;
        });

        Gate::define('delete-tag', function (User $user, Tag $tag) {
            return $user->is_admin;
        });

        // Media-specific gates
        Gate::define('upload-media', function (User $user) {
            return $user->is_admin;
        });

        Gate::define('edit-media', function (User $user, Media $media) {
            return $user->is_admin || $media->user_id === $user->id;
        });

        Gate::define('delete-media', function (User $user, Media $media) {
            return $user->is_admin || $media->user_id === $user->id;
        });

        // Meta management gates
        Gate::define('manage-meta', function (User $user) {
            return $user->is_admin;
        });

        // Cache management gates
        Gate::define('manage-cache', function (User $user) {
            return $user->is_admin;
        });

        // Maintenance mode gates
        Gate::define('manage-maintenance', function (User $user) {
            return $user->is_admin;
        });
    }
}