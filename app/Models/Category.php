<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'product_categories';
    protected $guarded = ['id'];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Static wrappers removed - use standard Eloquent method in controllers
    // - all()
    // - create()
    // - update()
    // - delete()
    // - count()

    // Explicit find override to filter active only?
    // Not recommended to override find() as arguments vary.
    // Use scopes instead if needed.
    // For now, controllers use where('is_active', 1) or find($id) (which returns active ones if ID matches, wait find($id) doesn't check active unless we construct it).
    // The previous find() checked is_active.
    // But findOrFail in controller does NOT check is_active.
    // I should add a global scope for is_active if I want to enforce it everywhere, 
    // OR just rely on controllers.
    // Given the refactor, I'll rely on controllers (I added where is_active active)
    // For find(), if ID exists but is inactive, Eloquent find returns it.
    // But legacy find($id) filtered active.
    // So current controller Category::find($id) might return inactive category.
    // I'll add a global scope for safety!

    protected static function booted()
    {
        static::addGlobalScope('active', function ($builder) {
            $builder->where('is_active', 1);
        });
    }
}
