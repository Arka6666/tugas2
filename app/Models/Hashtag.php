<?php
// app/Models/Hashtag.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The posts that belong to the hashtag.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
