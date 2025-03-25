<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'content',
        'post_id',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_website',
        'parent_id',
        'status',
        'ip_address',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the post that the comment belongs to.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who wrote the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment.
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('replies');
    }

    /**
     * Scope a query to only include approved comments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending comments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include spam comments.
     */
    public function scopeSpam($query)
    {
        return $query->where('status', 'spam');
    }

    /**
     * Scope a query to only include top-level comments.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the author name (user or guest).
     */
    public function getAuthorNameAttribute()
    {
        return $this->user_id ? $this->user->name : $this->guest_name;
    }

    /**
     * Get the author email (user or guest).
     */
    public function getAuthorEmailAttribute()
    {
        return $this->user_id ? $this->user->email : $this->guest_email;
    }

    /**
     * Check if the comment is from a registered user.
     */
    public function getIsRegisteredUserAttribute()
    {
        return !is_null($this->user_id);
    }

    /**
     * Check if the comment is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Approve the comment.
     */
    public function approve()
    {
        $this->update(['status' => 'approved']);
    }

    /**
     * Mark the comment as spam.
     */
    public function markAsSpam()
    {
        $this->update(['status' => 'spam']);
    }
}
