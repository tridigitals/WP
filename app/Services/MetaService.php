<?php

namespace App\Services;

use App\Models\Meta;
use Illuminate\Support\Str;

class MetaService
{
    /**
     * Create or update meta information for a model.
     */
    public function updateOrCreate($model, array $data): Meta
    {
        return $model->meta()->updateOrCreate(
            ['metable_id' => $model->id, 'metable_type' => get_class($model)],
            $this->validateMetaData($data)
        );
    }

    /**
     * Validate and sanitize meta data.
     */
    protected function validateMetaData(array $data): array
    {
        $maxLengths = config('cms.seo', [
            'meta_title_max_length' => 60,
            'meta_description_max_length' => 160
        ]);

        return array_merge($data, [
            'meta_title' => Str::limit($data['meta_title'] ?? '', $maxLengths['meta_title_max_length'], ''),
            'meta_description' => Str::limit($data['meta_description'] ?? '', $maxLengths['meta_description_max_length'], ''),
            'meta_keywords' => $this->sanitizeKeywords($data['meta_keywords'] ?? ''),
        ]);
    }

    /**
     * Generate schema.org markup for a model.
     */
    public function generateSchemaMarkup($model): array
    {
        $schema = [
            '@context' => 'https://schema.org',
        ];

        switch (class_basename($model)) {
            case 'Post':
                $schema = array_merge($schema, [
                    '@type' => 'Article',
                    'headline' => $model->title,
                    'description' => $model->excerpt ?? Str::limit(strip_tags($model->content), 160),
                    'datePublished' => $model->published_at?->toIso8601String(),
                    'dateModified' => $model->updated_at->toIso8601String(),
                    'author' => [
                        '@type' => 'Person',
                        'name' => $model->user?->name
                    ]
                ]);
                break;

            case 'Category':
                $schema = array_merge($schema, [
                    '@type' => 'CollectionPage',
                    'name' => $model->name,
                    'description' => $model->description
                ]);
                break;

            default:
                $schema['@type'] = 'WebPage';
        }

        return $schema;
    }

    /**
     * Analyze SEO score for meta information.
     */
    public function analyzeSeo(Meta $meta): array
    {
        $score = 0;
        $issues = [];
        $improvements = [];
        
        // Title analysis
        $titleLength = Str::length($meta->meta_title ?? '');
        if (!$meta->meta_title) {
            $issues[] = 'Meta title is missing';
        } elseif ($titleLength < 30) {
            $improvements[] = 'Meta title is too short (minimum 30 characters)';
        } elseif ($titleLength > 60) {
            $improvements[] = 'Meta title is too long (maximum 60 characters)';
        } else {
            $score += 25;
        }

        // Description analysis
        $descLength = Str::length($meta->meta_description ?? '');
        if (!$meta->meta_description) {
            $issues[] = 'Meta description is missing';
        } elseif ($descLength < 120) {
            $improvements[] = 'Meta description is too short (minimum 120 characters)';
        } elseif ($descLength > 160) {
            $improvements[] = 'Meta description is too long (maximum 160 characters)';
        } else {
            $score += 25;
        }

        // Keywords analysis
        if ($meta->meta_keywords) {
            $keywordCount = count(explode(',', $meta->meta_keywords));
            if ($keywordCount < 3) {
                $improvements[] = 'Consider adding more keywords (minimum 3)';
            } elseif ($keywordCount > 10) {
                $improvements[] = 'Too many keywords (maximum 10 recommended)';
            } else {
                $score += 15;
            }
        } else {
            $improvements[] = 'Consider adding meta keywords';
        }

        // Social meta analysis
        if ($this->hasSocialMeta($meta)) {
            $score += 20;
        } else {
            $improvements[] = 'Add social media meta tags for better sharing';
        }

        // Schema.org markup
        if ($meta->schema_markup) {
            $score += 15;
        } else {
            $improvements[] = 'Add Schema.org markup for better search results';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'improvements' => $improvements,
            'analysis' => [
                'title' => [
                    'length' => $titleLength,
                    'optimal' => $titleLength >= 30 && $titleLength <= 60
                ],
                'description' => [
                    'length' => $descLength,
                    'optimal' => $descLength >= 120 && $descLength <= 160
                ],
                'social_meta' => $this->hasSocialMeta($meta),
                'schema_markup' => !empty($meta->schema_markup)
            ]
        ];
    }

    /**
     * Check if meta has social media tags.
     */
    protected function hasSocialMeta(Meta $meta): bool
    {
        return !empty($meta->og_title) && 
               !empty($meta->og_description) && 
               !empty($meta->twitter_card);
    }

    /**
     * Sanitize keywords string.
     */
    protected function sanitizeKeywords(?string $keywords): ?string
    {
        if (!$keywords) {
            return null;
        }

        // Split keywords, trim, remove empty ones, and limit to 10
        $keywordArray = collect(explode(',', $keywords))
            ->map(fn($keyword) => trim($keyword))
            ->filter()
            ->take(10);

        return $keywordArray->implode(',');
    }

    /**
     * Generate meta tags HTML.
     */
    public function generateMetaTags(Meta $meta): string
    {
        $tags = [];

        // Basic meta tags
        if ($meta->meta_title) {
            $tags[] = "<title>{$meta->meta_title}</title>";
            $tags[] = "<meta name=\"title\" content=\"{$meta->meta_title}\">";
        }
        if ($meta->meta_description) {
            $tags[] = "<meta name=\"description\" content=\"{$meta->meta_description}\">";
        }
        if ($meta->meta_keywords) {
            $tags[] = "<meta name=\"keywords\" content=\"{$meta->meta_keywords}\">";
        }
        if ($meta->canonical_url) {
            $tags[] = "<link rel=\"canonical\" href=\"{$meta->canonical_url}\">";
        }
        if ($meta->robots) {
            $tags[] = "<meta name=\"robots\" content=\"{$meta->robots}\">";
        }

        // Open Graph tags
        if ($meta->og_title) {
            $tags[] = "<meta property=\"og:title\" content=\"{$meta->og_title}\">";
        }
        if ($meta->og_description) {
            $tags[] = "<meta property=\"og:description\" content=\"{$meta->og_description}\">";
        }
        if ($meta->og_image) {
            $tags[] = "<meta property=\"og:image\" content=\"{$meta->og_image}\">";
        }
        if ($meta->og_type) {
            $tags[] = "<meta property=\"og:type\" content=\"{$meta->og_type}\">";
        }

        // Twitter Card tags
        if ($meta->twitter_card) {
            $tags[] = "<meta name=\"twitter:card\" content=\"{$meta->twitter_card}\">";
        }
        if ($meta->twitter_title) {
            $tags[] = "<meta name=\"twitter:title\" content=\"{$meta->twitter_title}\">";
        }
        if ($meta->twitter_description) {
            $tags[] = "<meta name=\"twitter:description\" content=\"{$meta->twitter_description}\">";
        }
        if ($meta->twitter_image) {
            $tags[] = "<meta name=\"twitter:image\" content=\"{$meta->twitter_image}\">";
        }

        // Schema.org JSON-LD
        if ($meta->schema_markup) {
            $tags[] = "<script type=\"application/ld+json\">" . 
                     json_encode($meta->schema_markup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . 
                     "</script>";
        }

        return implode("\n", $tags);
    }
}