<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users first as they are needed for posts
        $this->call(UserSeeder::class);

        // Create categories and tags before posts
        $this->call(CategorySeeder::class);
        $this->call(TagSeeder::class);

        // Create posts last as they depend on users, categories, and tags
        $this->call(PostSeeder::class);

        // Create default settings
        $this->createDefaultSettings();
    }

    /**
     * Create default CMS settings.
     */
    private function createDefaultSettings(): void
    {
        $settings = [
            // General settings
            ['group' => 'general', 'key' => 'site_name', 'value' => 'Laravel React CMS', 'type' => 'string', 'is_autoload' => true],
            ['group' => 'general', 'key' => 'site_description', 'value' => 'A modern CMS built with Laravel and React', 'type' => 'string', 'is_autoload' => true],
            ['group' => 'general', 'key' => 'site_logo', 'value' => null, 'type' => 'string', 'is_autoload' => true],
            ['group' => 'general', 'key' => 'favicon', 'value' => null, 'type' => 'string', 'is_autoload' => true],

            // Theme settings
            ['group' => 'theme', 'key' => 'primary_color', 'value' => '#1a56db', 'type' => 'string', 'is_autoload' => true],
            ['group' => 'theme', 'key' => 'secondary_color', 'value' => '#7e3af2', 'type' => 'string', 'is_autoload' => true],
            ['group' => 'theme', 'key' => 'dark_mode', 'value' => 'false', 'type' => 'boolean', 'is_autoload' => true],

            // Content settings
            ['group' => 'content', 'key' => 'posts_per_page', 'value' => '10', 'type' => 'integer', 'is_autoload' => true],
            ['group' => 'content', 'key' => 'excerpt_length', 'value' => '250', 'type' => 'integer', 'is_autoload' => true],
            ['group' => 'content', 'key' => 'default_category', 'value' => '1', 'type' => 'integer', 'is_autoload' => true],

            // Comments settings
            ['group' => 'comments', 'key' => 'enable_comments', 'value' => 'true', 'type' => 'boolean', 'is_autoload' => true],
            ['group' => 'comments', 'key' => 'moderation_enabled', 'value' => 'true', 'type' => 'boolean', 'is_autoload' => true],
            ['group' => 'comments', 'key' => 'allow_guest_comments', 'value' => 'true', 'type' => 'boolean', 'is_autoload' => true],

            // SEO settings
            ['group' => 'seo', 'key' => 'meta_description', 'value' => 'A modern CMS built with Laravel and React', 'type' => 'string', 'is_autoload' => true],
            ['group' => 'seo', 'key' => 'meta_keywords', 'value' => 'cms,laravel,react,blog', 'type' => 'string', 'is_autoload' => true],
            ['group' => 'seo', 'key' => 'google_analytics_id', 'value' => null, 'type' => 'string', 'is_autoload' => true]
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::create($setting);
        }
    }
}
