<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['title' => 'array', 'summary' => 'array', 'description' => 'array', 'seo_title' => 'array', 'seo_description' => 'array', 'is_published' => 'boolean', 'price_from' => 'integer'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /** @return array<int, int> */
    public function subtreeIds(): array
    {
        $ids = [$this->id];
        $frontier = [$this->id];

        while ($frontier !== []) {
            $children = self::whereIn('parent_id', $frontier)->pluck('id')->all();
            $frontier = array_values(array_diff($children, $ids));
            array_push($ids, ...$frontier);
        }

        return $ids;
    }

    public function pathTitle(string $locale): string
    {
        $parts = [];
        $category = $this;
        $seen = [];

        while ($category !== null && ! isset($seen[$category->id])) {
            $seen[$category->id] = true;
            $parts[] = $category->title[$locale] ?? ($locale === 'en' ? Str::headline($category->slug) : ($category->title['ru'] ?? ''));
            $category = $category->parent;
        }

        return implode(' / ', array_reverse($parts));
    }
}
