<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'patient_id',
        'type',
        'message',
        'alert_time',
        'is_acknowledged',
        'acknowledged_by_user_id',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'alert_time' => 'datetime',
            'acknowledged_at' => 'datetime',
            'is_acknowledged' => 'boolean',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by_user_id');
    }
}
