<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Post;
use App\Models\Media;
use App\Models\Meta;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        // Create test user
        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        if (app()->environment('local', 'testing')) {
            // Create additional test users
            User::factory(5)->create();

            // Create categories with meta
            Category::factory(5)
                ->has(
                    Meta::factory()->state(function (array $attributes, Category $category) {
                        return [
                            'meta_title' => $category->name,
                            'meta_description' => $category->description,
                        ];
                    })
                )
                ->create();

            // Create tags with meta
            Tag::factory(10)
                ->has(
                    Meta::factory()->state(function (array $attributes, Tag $tag) {
                        return [
                            'meta_title' => $tag->name,
                            'meta_description' => "Posts tagged with {$tag->name}",
                        ];
                    })
                )
                ->create();

            // Create posts with relationships
            Post::factory(20)
                ->state(function (array $attributes) {
                    return [
                        'user_id' => User::inRandomOrder()->first()->id,
                        'category_id' => Category::inRandomOrder()->first()->id,
                    ];
                })
                ->has(
                    Meta::factory()->state(function (array $attributes, Post $post) {
                        return [
                            'meta_title' => $post->title,
                            'meta_description' => substr(strip_tags($post->content), 0, 160),
                            'og_title' => $post->title,
                            'twitter_card' => 'summary_large_image',
                        ];
                    })
                )
                ->afterCreating(function (Post $post) {
                    // Attach random tags
                    $post->tags()->attach(
                        Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id')->toArray()
                    );

                    // Create featured image
                    if (rand(0, 1)) {
                        Media::factory()->create([
                            'user_id' => $post->user_id,
                            'mime_type' => 'image/jpeg',
                            'title' => "Featured image for {$post->title}",
                        ]);
                    }
                })
                ->create();

            // Create sample media
            Media::factory(10)->create([
                'user_id' => User::inRandomOrder()->first()->id,
            ]);

            // Create additional meta data
            Meta::factory(5)->create([
                'metable_type' => User::class,
                'metable_id' => function () {
                    return User::inRandomOrder()->first()->id;
                },
            ]);
        }

        // Run the CMS seeder for sample content
        $this->call([
            CmsSeeder::class,
        ]);
    }
}
