<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TripBooking extends Model
{
    protected $table = 'trip_bookings';
    protected $guarded = ['id'];
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    } // via schedule likely
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
