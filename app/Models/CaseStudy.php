<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CaseStudy extends Model
{
    protected $guarded = [];

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)->orderBy('sort_order')->orderBy('name');
    }

    protected function casts(): array
    {
        return ['title' => 'array', 'summary' => 'array', 'description' => 'array', 'seo_title' => 'array', 'seo_description' => 'array', 'is_published' => 'boolean'];
    }
}
