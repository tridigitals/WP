<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'admin',
            'bio' => 'System administrator',
            'avatar' => null,
            'website' => 'https://example.com',
            'social_media_links' => json_encode([
                'twitter' => 'https://twitter.com/admin',
                'linkedin' => 'https://linkedin.com/in/admin'
            ])
        ]);

        // Create author users
        User::factory()->count(5)->create([
            'role' => 'author',
            'bio' => fake()->paragraph(),
            'website' => fn() => fake()->optional(0.7)->url(),
            'social_media_links' => fn() => json_encode([
                'twitter' => fake()->optional(0.8)->url(),
                'linkedin' => fake()->optional(0.8)->url(),
                'github' => fake()->optional(0.6)->url()
            ])
        ]);
    }
}
