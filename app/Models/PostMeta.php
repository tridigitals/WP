<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PostMeta extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'post_id',
        'key',
        'value'
    ];

    /**
     * Get the post that owns the meta.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}