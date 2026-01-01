<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    protected $table = 'course_enrollments';
    protected $guarded = ['id'];
    public function course()
    {
        return $this->belongsTo(Course::class);
    } // via schedule?
    public function student()
    {
        return $this->belongsTo(Customer::class, 'student_id');
    }
}
