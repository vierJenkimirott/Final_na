<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'offense_category_id',
        'violation_name',
        'description',
        'default_penalty'
    ];

    // Define the relationship with the OffenseCategory model
    public function offenseCategory()
    {
        return $this->belongsTo(OffenseCategory::class, 'offense_category_id');
    }

    // Define the relationship with the Violation model
    public function violations()
    {
        return $this->hasMany(Violation::class, 'violation_type_id');
    }
} 