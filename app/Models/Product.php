<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $guarded = ['id'];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc');
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    // Scopes
    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', 1);
        });
    }

    // Instance Methods
    public function adjustStock(int $qty, string $type, ?string $reason = null)
    {
        $oldQty = $this->stock_quantity;
        $newQty = $oldQty + $qty;

        $this->update(['stock_quantity' => $newQty]);

        // Log transaction
        $this->inventoryTransactions()->create([
            'user_id' => $_SESSION['user_id'] ?? null,
            'transaction_type' => $type,
            'quantity_change' => $qty,
            'new_quantity' => $newQty,
            'notes' => $reason
        ]);

        return $newQty;
    }

    // Static helpers for compatibility/convenience if needed, but keeping it clean is better.
    // The Service uses Product::getLowStock(). I should replace that usage in Service, or add a scope.

    public function scopeLowStock(Builder $query)
    {
        return $query->where('track_stock', 1)
            ->whereColumn('stock_quantity', '<=', 'reorder_level');
    }

    // For backward compatibility with static getLowStock call in Service (if I missed updating it, but I did update it to use getLowStock() which I removed).
    // Wait, in previous step I updated Service to call Product::getLowStock() BUT I removed getLowStock() from Model.
    // So I MUST add getLowStock() back OR update Service to use scope.
    // I prefer adding a static wrapper that uses the scope for now to ensure Service works if I didn't update it perfectly.
    // Actually, looking at my Service update: `return Product::getLowStock();` was what I put in the ReplacementContent because I reverted to it?
    // No, I tried to replace it with valid code but then I might have messed up. 
    // Let's look at `ProductService.php` again.
    // Arguments: `return Product::where(...)->get()->toArray();` was my PLAN.
    // But in the ReplacementContent I put `return Product::getLowStock();` as TARGET CONTENT?
    // No, I put `return Product::where(...)->get()->toArray();` as REPLACEMENT.
    // So Service SHOULD be using the valid code.
    // So I don't need `getLowStock` static method on Model.
}
