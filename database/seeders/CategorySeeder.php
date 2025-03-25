<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main categories
        $technology = Category::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Articles about technology and innovation'
        ]);

        $lifestyle = Category::create([
            'name' => 'Lifestyle',
            'slug' => 'lifestyle',
            'description' => 'Articles about lifestyle and personal development'
        ]);

        $business = Category::create([
            'name' => 'Business',
            'slug' => 'business',
            'description' => 'Articles about business and entrepreneurship'
        ]);

        // Technology subcategories
        Category::create([
            'name' => 'Programming',
            'slug' => 'programming',
            'description' => 'Articles about programming and software development',
            'parent_id' => $technology->id
        ]);

        Category::create([
            'name' => 'Web Development',
            'slug' => 'web-development',
            'description' => 'Articles about web development and design',
            'parent_id' => $technology->id
        ]);

        Category::create([
            'name' => 'Artificial Intelligence',
            'slug' => 'artificial-intelligence',
            'description' => 'Articles about AI and machine learning',
            'parent_id' => $technology->id
        ]);

        // Lifestyle subcategories
        Category::create([
            'name' => 'Health & Wellness',
            'slug' => 'health-wellness',
            'description' => 'Articles about health and wellness',
            'parent_id' => $lifestyle->id
        ]);

        Category::create([
            'name' => 'Travel',
            'slug' => 'travel',
            'description' => 'Articles about travel and adventure',
            'parent_id' => $lifestyle->id
        ]);

        // Business subcategories
        Category::create([
            'name' => 'Marketing',
            'slug' => 'marketing',
            'description' => 'Articles about marketing and advertising',
            'parent_id' => $business->id
        ]);

        Category::create([
            'name' => 'Startups',
            'slug' => 'startups',
            'description' => 'Articles about startups and entrepreneurship',
            'parent_id' => $business->id
        ]);
    }
}
