<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Meta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_markup',
        'custom_meta'
    ];

    protected $casts = [
        'schema_markup' => 'array',
        'custom_meta' => 'array'
    ];

    // Polymorphic relationship
    public function metable()
    {
        return $this->morphTo();
    }

    // SEO Meta Tags Generation
    public function generateMetaTags(): array
    {
        $tags = [];

        // Basic meta tags
        if ($this->meta_title) {
            $tags[] = ['name' => 'title', 'content' => $this->meta_title];
        }
        if ($this->meta_description) {
            $tags[] = ['name' => 'description', 'content' => $this->meta_description];
        }
        if ($this->meta_keywords) {
            $tags[] = ['name' => 'keywords', 'content' => $this->meta_keywords];
        }
        if ($this->canonical_url) {
            $tags[] = ['rel' => 'canonical', 'href' => $this->canonical_url];
        }
        if ($this->robots) {
            $tags[] = ['name' => 'robots', 'content' => $this->robots];
        }

        // Open Graph tags
        if ($this->og_title) {
            $tags[] = ['property' => 'og:title', 'content' => $this->og_title];
        }
        if ($this->og_description) {
            $tags[] = ['property' => 'og:description', 'content' => $this->og_description];
        }
        if ($this->og_image) {
            $tags[] = ['property' => 'og:image', 'content' => $this->og_image];
        }
        if ($this->og_type) {
            $tags[] = ['property' => 'og:type', 'content' => $this->og_type];
        }

        // Twitter Card tags
        if ($this->twitter_card) {
            $tags[] = ['name' => 'twitter:card', 'content' => $this->twitter_card];
        }
        if ($this->twitter_title) {
            $tags[] = ['name' => 'twitter:title', 'content' => $this->twitter_title];
        }
        if ($this->twitter_description) {
            $tags[] = ['name' => 'twitter:description', 'content' => $this->twitter_description];
        }
        if ($this->twitter_image) {
            $tags[] = ['name' => 'twitter:image', 'content' => $this->twitter_image];
        }

        // Custom meta tags
        if ($this->custom_meta) {
            foreach ($this->custom_meta as $name => $content) {
                $tags[] = ['name' => $name, 'content' => $content];
            }
        }

        return $tags;
    }

    // Schema.org JSON-LD
    public function getSchemaMarkup(): ?string
    {
        if (!$this->schema_markup) {
            return null;
        }

        return '<script type="application/ld+json">' .
               json_encode($this->schema_markup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) .
               '</script>';
    }

    // Helper Methods
    public function setMetaTitle(?string $title): void
    {
        $this->meta_title = $title;
        $this->og_title = $title;
        $this->twitter_title = $title;
        $this->save();
    }

    public function setMetaDescription(?string $description): void
    {
        $this->meta_description = $description;
        $this->og_description = $description;
        $this->twitter_description = $description;
        $this->save();
    }

    public function setMetaImage(?string $image): void
    {
        $this->og_image = $image;
        $this->twitter_image = $image;
        $this->save();
    }

    public function addCustomMeta(string $name, string $content): void
    {
        $customMeta = $this->custom_meta ?? [];
        $customMeta[$name] = $content;
        $this->custom_meta = $customMeta;
        $this->save();
    }

    public function removeCustomMeta(string $name): void
    {
        if ($this->custom_meta && isset($this->custom_meta[$name])) {
            unset($this->custom_meta[$name]);
            $this->save();
        }
    }

    // Render Methods
    public function renderMetaTags(): string
    {
        return collect($this->generateMetaTags())
            ->map(function ($tag) {
                $attributes = collect($tag)
                    ->map(fn($value, $key) => sprintf('%s="%s"', $key, htmlspecialchars($value)))
                    ->join(' ');
                return "<meta {$attributes}>";
            })
            ->join("\n");
    }
}
