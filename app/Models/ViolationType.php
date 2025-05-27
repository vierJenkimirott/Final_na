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
        'default_penalty',
        'severity_id'
    ];
    
    protected $appends = ['severity'];

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
    
    // Define the relationship with the Severity model
    public function severityRelation()
    {
        return $this->belongsTo(Severity::class, 'severity_id');
    }
    
    // Get the severity attribute
    public function getSeverityAttribute()
    {
        return $this->severityRelation ? $this->severityRelation->severity_name : null;
    }
}