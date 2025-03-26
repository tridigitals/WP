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
            'bio' => 'System administrator',
            'avatar' => null,
            'website' => 'https://example.com',
            'social_media_links' => json_encode([
                'twitter' => 'https://twitter.com/admin',
                'linkedin' => 'https://linkedin.com/in/admin'
            ])
        ]);
    }
}
