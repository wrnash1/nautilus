<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $guarded = ['id'];

    // Static wrappers removed - use standard Eloquent method in controllers

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', 1);
        });
    }
}
