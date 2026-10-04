<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)->orderBy('sort_order')->orderBy('name');
    }

    protected function casts(): array
    {
        return ['title' => 'array', 'summary' => 'array', 'seo_title' => 'array', 'seo_description' => 'array', 'is_published' => 'boolean', 'price_from' => 'integer'];
    }
}
