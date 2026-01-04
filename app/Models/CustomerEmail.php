<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomerEmail extends Model
{
    protected $table = 'customer_emails';
    protected $guarded = ['id'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
