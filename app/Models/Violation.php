<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentDetails;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'sex',
        'violation_date',
        'violation_type_id',
        'severity',
        'offense',
        'penalty',
        'consequence',
        'status',
    ];

    // Define the relationship with the User model
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'student_id');
    }
    
    // Define the relationship with StudentDetails through User
    public function studentDetails()
    {
        return $this->hasOneThrough(
            StudentDetails::class,
            User::class,
            'student_id', // Foreign key on users table
            'user_id',    // Foreign key on student_details table
            'student_id', // Local key on violations table
            'id'          // Local key on users table
        );
    }

    // Define the relationship with the ViolationType model
    public function violationType()
    {
        return $this->belongsTo(ViolationType::class, 'violation_type_id');
    }

    // Define the relationship with the OffenseCategory model through ViolationType
    public function offenseCategory()
    {
        return $this->hasOneThrough(
            OffenseCategory::class,
            ViolationType::class,
            'id', // Foreign key on violation_types table
            'id', // Foreign key on offense_categories table
            'violation_type_id', // Local key on violations table
            'offense_category_id' // Local key on violation_types table
        );
    }
}