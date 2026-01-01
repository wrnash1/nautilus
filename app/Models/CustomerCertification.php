<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomerCertification extends Model
{
    protected $table = 'customer_certifications';
    protected $guarded = ['id'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function certification()
    {
        return $this->belongsTo(Certification::class);
    }
    public function agency()
    {
        return $this->belongsTo(CertificationAgency::class);
    }
}
