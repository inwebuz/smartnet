<?php

namespace App\Models;

use Database\Factories\AdditionalServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdditionalService extends Model
{
    /** @use HasFactory<AdditionalServiceFactory> */
    use HasFactory;

    protected $guarded = [];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withPivot(['price', 'sort_order']);
    }

    protected function casts(): array
    {
        return ['title' => 'array', 'description' => 'array', 'is_active' => 'boolean'];
    }
}
