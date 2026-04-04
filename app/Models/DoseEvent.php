<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoseEvent extends Model
{
    protected $fillable = [
        'patient_id',
        'medication_schedule_id',
        'status',
        'event_time',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'event_time' => 'datetime',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicationSchedule()
    {
        return $this->belongsTo(MedicationSchedule::class);
    }
}
