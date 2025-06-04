<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDetails extends Model
{
    use HasFactory;

    protected $table = 'student_details';

    protected $fillable = [
        'user_id',
        'student_id',
        'batch',
        'group',
        'student_number',
        'training_code',
        'grade',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
