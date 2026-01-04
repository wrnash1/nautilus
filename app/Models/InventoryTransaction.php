<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $table = 'inventory_transactions';
    protected $guarded = ['id'];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
