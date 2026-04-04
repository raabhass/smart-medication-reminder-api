<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationSchedule extends Model
{
    protected $fillable = [
        'patient_id',
        'medication_name',
        'dosage',
        'frequency',
        'scheduled_time',
        'instructions',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doseEvents()
    {
        return $this->hasMany(DoseEvent::class);
    }
}
