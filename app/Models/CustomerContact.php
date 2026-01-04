<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    protected $table = 'customer_contacts';
    protected $guarded = ['id'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
