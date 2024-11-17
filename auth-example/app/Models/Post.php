<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes() // Changed to plural
    {
        return $this->hasMany(Like::class); // Ensure 'Like' is correctly capitalized
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
