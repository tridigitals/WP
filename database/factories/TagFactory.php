<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        
        return [
            'user_id' => User::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->optional(0.7)->sentence,
            'count' => 0,
            'meta_data' => [
                'color' => $this->faker->hexColor(),
                'featured' => false,
                'related_tags' => [],
                'last_used_at' => null
            ],
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Add meta information to the tag.
     */
    public function withMeta(array $meta = []): static
    {
        return $this->afterCreating(function (Tag $tag) use ($meta) {
            $tag->meta()->create(array_merge([
                'meta_title' => $tag->name,
                'meta_description' => $tag->description ?? "Posts tagged with {$tag->name}",
                'meta_keywords' => $tag->name,
                'og_title' => $tag->name,
                'og_description' => $tag->description ?? "Posts tagged with {$tag->name}",
                'twitter_card' => 'summary',
                'schema_markup' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebPage',
                    'name' => $tag->name,
                    'description' => $tag->description,
                ]
            ], $meta));
        });
    }

    /**
     * Set tag as featured.
     */
    public function featured(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'meta_data' => array_merge($attributes['meta_data'], [
                    'featured' => true
                ]),
            ];
        });
    }

    /**
     * Add related tags.
     */
    public function withRelatedTags(int $count = 3): static
    {
        return $this->afterCreating(function (Tag $tag) use ($count) {
            $relatedTags = Tag::factory()->count($count)->create();
            
            $tag->update([
                'meta_data' => array_merge($tag->meta_data, [
                    'related_tags' => $relatedTags->pluck('id')->toArray()
                ])
            ]);
        });
    }

    /**
     * Add sample posts to the tag.
     */
    public function withPosts(int $count = 3): static
    {
        return $this->afterCreating(function (Tag $tag) use ($count) {
            $posts = \App\Models\Post::factory()
                ->count($count)
                ->create()
                ->each(function ($post) use ($tag) {
                    $post->tags()->attach($tag->id);
                });

            // Update tag count
            $tag->update([
                'count' => $count,
                'meta_data' => array_merge($tag->meta_data, [
                    'last_used_at' => now()->toDateTimeString()
                ])
            ]);
        });
    }
}