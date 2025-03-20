<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'post_type',
        'status',
        'visibility',
        'password',
        'featured_image',
        'published_at',
        'meta_data',
        'layout_data'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'layout_data' => 'array',
        'published_at' => 'datetime'
    ];

    protected $dates = [
        'published_at'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function meta(): MorphOne
    {
        return $this->morphOne(Meta::class, 'metable');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('published_at', '>', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    // Helper methods
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->published_at > now();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
