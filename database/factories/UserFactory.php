<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_admin' => false,
            'preferences' => [
                'theme' => 'light',
                'notifications' => true,
                'email_notifications' => true,
                'two_factor_enabled' => false,
                'language' => 'en',
                'timezone' => 'UTC',
                'posts_per_page' => 10,
            ],
            'meta_data' => [
                'last_login_at' => null,
                'last_login_ip' => null,
                'login_count' => 0,
                'bio' => $this->faker->optional()->paragraph,
                'social_links' => [
                    'twitter' => $this->faker->optional()->url,
                    'facebook' => $this->faker->optional()->url,
                    'linkedin' => $this->faker->optional()->url,
                    'github' => $this->faker->optional()->url,
                ],
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($this->faker->name),
            ],
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_admin' => true,
                'preferences' => array_merge($attributes['preferences'], [
                    'admin_notifications' => true,
                    'dashboard_widgets' => [
                        'quick_stats' => true,
                        'recent_posts' => true,
                        'activity_log' => true,
                    ],
                ]),
            ];
        });
    }

    /**
     * Indicate that the user's email is unverified.
     */
    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Configure two-factor authentication.
     */
    public function withTwoFactor(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'preferences' => array_merge($attributes['preferences'], [
                    'two_factor_enabled' => true,
                    'two_factor_secret' => Str::random(32),
                    'two_factor_recovery_codes' => collect(range(1, 8))->map(fn () => Str::random(10))->all(),
                ]),
            ];
        });
    }

    /**
     * Add profile information.
     */
    public function withProfile(array $profile = []): static
    {
        return $this->state(function (array $attributes) use ($profile) {
            return [
                'meta_data' => array_merge($attributes['meta_data'], [
                    'bio' => $profile['bio'] ?? $this->faker->paragraph,
                    'location' => $profile['location'] ?? $this->faker->city,
                    'website' => $profile['website'] ?? $this->faker->url,
                    'company' => $profile['company'] ?? $this->faker->company,
                    'position' => $profile['position'] ?? $this->faker->jobTitle,
                ], $profile),
            ];
        });
    }

    /**
     * Generate API tokens.
     */
    public function withApiTokens(int $count = 1): static
    {
        return $this->afterCreating(function (User $user) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $user->createToken("api-token-{$i}")->plainTextToken;
            }
        });
    }

    /**
     * Add some posts.
     */
    public function withPosts(int $count = 3): static
    {
        return $this->afterCreating(function (User $user) use ($count) {
            \App\Models\Post::factory()
                ->count($count)
                ->create(['user_id' => $user->id]);
        });
    }

    /**
     * Add login history.
     */
    public function withLoginHistory(int $count = 5): static
    {
        return $this->state(function (array $attributes) use ($count) {
            return [
                'meta_data' => array_merge($attributes['meta_data'], [
                    'last_login_at' => now()->toDateTimeString(),
                    'last_login_ip' => $this->faker->ipv4,
                    'login_count' => $count,
                    'login_history' => collect(range(1, $count))->map(fn ($i) => [
                        'timestamp' => now()->subDays($i)->toDateTimeString(),
                        'ip' => $this->faker->ipv4,
                        'user_agent' => $this->faker->userAgent,
                    ])->all(),
                ]),
            ];
        });
    }

    /**
     * Create a common test user.
     */
    public function common(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => now(),
                'meta_data' => array_merge($attributes['meta_data'], [
                    'bio' => 'A test user for common scenarios',
                    'avatar' => 'https://ui-avatars.com/api/?name=Test+User',
                ]),
            ];
        })->withLoginHistory();
    }
}
