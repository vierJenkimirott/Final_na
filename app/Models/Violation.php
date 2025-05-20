<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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