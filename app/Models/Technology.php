<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Technology extends Model
{
    protected $guarded = [];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function caseStudies(): BelongsToMany
    {
        return $this->belongsToMany(CaseStudy::class);
    }
}
