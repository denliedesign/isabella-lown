<?php

// app/Models/Media.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Media extends Model
{
    protected $fillable = [
        'type', 'path', 'title',
        'sort_order', 'created_by',
        'tag', 'style', 'embed_html',
    ];

    public function scopePublished(Builder $q): Builder
    {
        return $q
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    public function scopeTag($q, string $tag)
    {
        return $q->where('tag', $tag);
    }

}

