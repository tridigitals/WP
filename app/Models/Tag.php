<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'count',
        'meta_data'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'count' => 'integer'
    ];

    // Relationships
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    public function meta(): MorphOne
    {
        return $this->morphOne(Meta::class, 'metable');
    }

    // Scopes
    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('count', 'desc')->limit($limit);
    }

    public function scopeWithUsage($query)
    {
        return $query->where('count', '>', 0);
    }

    // Events
    protected static function booted()
    {
        static::created(function ($tag) {
            $tag->updateCount();
        });

        static::deleted(function ($tag) {
            $tag->updateCount();
        });
    }

    // Helper methods
    public function updateCount(): void
    {
        $this->count = $this->posts()->count();
        $this->saveQuietly();
    }

    public function incrementCount(): void
    {
        $this->increment('count');
    }

    public function decrementCount(): void
    {
        $this->decrement('count');
    }
}
