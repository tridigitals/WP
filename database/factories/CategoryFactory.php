<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

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
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence,
            'order' => 0,
            'featured' => false,
            'meta_data' => [
                'icon' => $this->faker->randomElement([
                    'fas fa-folder',
                    'fas fa-book',
                    'fas fa-newspaper',
                    'fas fa-photo-video',
                    'fas fa-pencil-alt'
                ]),
                'color' => $this->faker->hexColor(),
                'show_in_menu' => true,
                'posts_count' => 0
            ],
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the category is featured.
     */
    public function featured(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'featured' => true,
            ];
        });
    }

    /**
     * Set the parent category.
     */
    public function child(Category $parent = null): static
    {
        return $this->state(function (array $attributes) use ($parent) {
            return [
                'parent_id' => $parent ? $parent->id : Category::factory(),
                'order' => Category::where('parent_id', $parent?->id)->max('order') + 1,
            ];
        });
    }

    /**
     * Create category with children.
     */
    public function withChildren(int $count = 2): static
    {
        return $this->afterCreating(function (Category $category) use ($count) {
            Category::factory()
                ->count($count)
                ->child($category)
                ->create();
        });
    }

    /**
     * Add meta information to the category.
     */
    public function withMeta(array $meta = []): static
    {
        return $this->afterCreating(function (Category $category) use ($meta) {
            $category->meta()->create(array_merge([
                'meta_title' => $category->name,
                'meta_description' => $category->description,
                'meta_keywords' => implode(', ', $this->faker->words(5)),
                'og_title' => $category->name,
                'og_description' => $category->description,
                'twitter_card' => 'summary',
                'schema_markup' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => $category->name,
                    'description' => $category->description,
                ]
            ], $meta));
        });
    }

    /**
     * Add sample posts to the category.
     */
    public function withPosts(int $count = 3): static
    {
        return $this->afterCreating(function (Category $category) use ($count) {
            $posts = \App\Models\Post::factory()
                ->count($count)
                ->create(['category_id' => $category->id]);

            // Update meta_data posts_count
            $category->update([
                'meta_data' => array_merge($category->meta_data, [
                    'posts_count' => $count
                ])
            ]);
        });
    }

    /**
     * Set a specific order for the category.
     */
    public function withOrder(int $order): static
    {
        return $this->state(function (array $attributes) use ($order) {
            return [
                'order' => $order,
            ];
        });
    }

    /**
     * Hide category from menu.
     */
    public function hideFromMenu(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'meta_data' => array_merge($attributes['meta_data'], [
                    'show_in_menu' => false
                ]),
            ];
        });
    }

    /**
     * Create a complete category hierarchy.
     */
    public function withFullHierarchy(): static
    {
        return $this->withChildren(2)
            ->withMeta()
            ->withPosts(3)
            ->afterCreating(function (Category $category) {
                // Create grandchildren for each child
                $category->children->each(function ($child) {
                    Category::factory()
                        ->count(2)
                        ->child($child)
                        ->withMeta()
                        ->withPosts(2)
                        ->create();
                });
            });
    }
}