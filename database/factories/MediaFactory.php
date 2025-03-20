<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = Str::random(40) . '.jpg';
        $mimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'application/msword',
            'text/plain'
        ];

        return [
            'user_id' => User::factory(),
            'file_name' => $fileName,
            'original_name' => 'original_' . $fileName,
            'mime_type' => $this->faker->randomElement($mimeTypes),
            'extension' => 'jpg',
            'size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'path' => 'media/' . $fileName,
            'disk' => 'public',
            'title' => $this->faker->words(3, true),
            'alt_text' => $this->faker->sentence,
            'caption' => $this->faker->optional()->sentence,
            'description' => $this->faker->optional()->paragraph,
            'status' => 'ready',
            'meta_data' => function (array $attributes) {
                if (str_starts_with($attributes['mime_type'], 'image/')) {
                    return [
                        'width' => $this->faker->numberBetween(800, 2400),
                        'height' => $this->faker->numberBetween(600, 1600),
                        'colors' => [
                            '#' . $this->faker->hexColor(),
                            '#' . $this->faker->hexColor(),
                            '#' . $this->faker->hexColor(),
                        ]
                    ];
                }
                return [];
            },
            'responsive_images' => function (array $attributes) {
                if (str_starts_with($attributes['mime_type'], 'image/')) {
                    return [
                        'thumbnail' => 'media/responsive/' . pathinfo($fileName, PATHINFO_FILENAME) . '-150x150.jpg',
                        'small' => 'media/responsive/' . pathinfo($fileName, PATHINFO_FILENAME) . '-300x300.jpg',
                        'medium' => 'media/responsive/' . pathinfo($fileName, PATHINFO_FILENAME) . '-600x600.jpg',
                        'large' => 'media/responsive/' . pathinfo($fileName, PATHINFO_FILENAME) . '-1200x1200.jpg'
                    ];
                }
                return null;
            },
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Indicate that the media is an image.
     */
    public function image(): static
    {
        return $this->state(function (array $attributes) {
            $fileName = Str::random(40) . '.jpg';
            return [
                'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/gif']),
                'extension' => 'jpg',
                'file_name' => $fileName,
                'original_name' => 'image_' . $fileName,
                'meta_data' => [
                    'width' => $this->faker->numberBetween(800, 2400),
                    'height' => $this->faker->numberBetween(600, 1600),
                    'colors' => [
                        '#' . $this->faker->hexColor(),
                        '#' . $this->faker->hexColor(),
                        '#' . $this->faker->hexColor(),
                    ]
                ],
                'responsive_images' => [
                    'thumbnail' => 'media/responsive/' . pathinfo($fileName, PATHINFO_FILENAME) . '-150x150.jpg',
                    'small' => 'media/responsive/' . pathinfo($fileName, PATHINFO_FILENAME) . '-300x300.jpg',
                    'medium' => 'media/responsive/' . pathinfo($fileName, PATHINFO_FILENAME) . '-600x600.jpg',
                    'large' => 'media/responsive/' . pathinfo($fileName, PATHINFO_FILENAME) . '-1200x1200.jpg'
                ]
            ];
        });
    }

    /**
     * Indicate that the media is a document.
     */
    public function document(): static
    {
        return $this->state(function (array $attributes) {
            $fileName = Str::random(40) . '.pdf';
            return [
                'mime_type' => $this->faker->randomElement([
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ]),
                'extension' => 'pdf',
                'file_name' => $fileName,
                'original_name' => 'document_' . $fileName,
                'meta_data' => [
                    'pages' => $this->faker->numberBetween(1, 50)
                ],
                'responsive_images' => null
            ];
        });
    }

    /**
     * Indicate that the media has been optimized.
     */
    public function optimized(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'meta_data' => array_merge($attributes['meta_data'] ?? [], [
                    'optimized' => true,
                    'optimized_at' => now()->toDateTimeString(),
                    'original_size' => $attributes['size'],
                    'optimized_size' => (int) ($attributes['size'] * 0.7)
                ])
            ];
        });
    }
}