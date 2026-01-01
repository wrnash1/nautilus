<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $table = 'certifications';
    protected $guarded = ['id'];
    public function agency()
    {
        return $this->belongsTo(CertificationAgency::class, 'agency_id');
    }
}
