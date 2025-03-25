<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get all posts that have this tag.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * Get all published posts that have this tag.
     */
    public function publishedPosts()
    {
        return $this->posts()->published();
    }

    /**
     * Get the URL to the tag archive.
     */
    public function getUrlAttribute()
    {
        return url("/tags/{$this->slug}");
    }

    /**
     * Get the post count for this tag.
     */
    public function getPostCountAttribute()
    {
        return $this->posts()->count();
    }

    /**
     * Get the published post count for this tag.
     */
    public function getPublishedPostCountAttribute()
    {
        return $this->publishedPosts()->count();
    }
}
