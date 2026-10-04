<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['title' => 'array', 'summary' => 'array', 'is_published' => 'boolean'];
    }
}
