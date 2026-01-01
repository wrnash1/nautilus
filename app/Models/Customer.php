<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    protected $guarded = ['id'];

    // Relationships
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function phones()
    {
        return $this->hasMany(CustomerPhone::class);
    }

    public function emails()
    {
        return $this->hasMany(CustomerEmail::class);
    }

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function certifications()
    {
        return $this->hasMany(CustomerCertification::class);
    }

    public function equipment()
    {
        return $this->hasMany(CustomerEquipment::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Scopes
    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', 1);
        });
    }

    // Search helper (Static method using scope logic)
    public static function search(string $query, int $limit = 20)
    {
        return static::where('is_active', 1)
            ->where(function ($q) use ($query) {
                $term = "%{$query}%";
                $q->where('first_name', 'LIKE', $term)
                    ->orWhere('last_name', 'LIKE', $term)
                    ->orWhere('email', 'LIKE', $term)
                    ->orWhere('phone', 'LIKE', $term)
                    ->orWhere('company_name', 'LIKE', $term);
            })
            ->limit($limit)
            ->get();
    }
}
