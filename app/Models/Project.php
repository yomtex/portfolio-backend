<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title', 'slug', 'short_description', 'long_description',
        'stack', 'github_link', 'demo_link', 'video_link', 'images','is_published'
    ];

    protected $casts = [
        'stack' => 'array',
        'images' => 'array',
        'is_published' => 'boolean',
    ];
}
