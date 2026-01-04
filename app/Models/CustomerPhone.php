<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomerPhone extends Model
{
    protected $table = 'customer_phones';
    protected $guarded = ['id'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
