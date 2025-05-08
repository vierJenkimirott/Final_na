<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'description'
    ];

    // Define the relationship with the ViolationType model
    public function violationTypes()
    {
        return $this->hasMany(ViolationType::class, 'offense_category_id');
    }
} 