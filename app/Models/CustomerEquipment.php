<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomerEquipment extends Model
{
    protected $table = 'customer_equipment';
    protected $guarded = ['id'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
