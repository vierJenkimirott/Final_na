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
        if (!$this->relationLoaded('severityRelation')) {
            $this->load('severityRelation');
        }
        return $this->severityRelation ? $this->severityRelation->severity_name : null;
    }

    // Scope for consistent sorting by severity and name
    public function scopeOrderBySeverityAndName($query)
    {
        return $query->join('severities', 'violation_types.severity_id', '=', 'severities.id')
            ->orderByRaw("FIELD(severities.severity_name, 'Low', 'Medium', 'High', 'Very High')")
            ->orderBy('violation_types.violation_name')
            ->select('violation_types.*');
    }
}