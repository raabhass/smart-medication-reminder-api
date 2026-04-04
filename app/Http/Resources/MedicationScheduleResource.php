<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'patient_id'       => $this->patient_id,
            'medication_name'  => $this->medication_name,
            'dosage'           => $this->dosage,
            'frequency'        => $this->frequency,
            'scheduled_time'   => $this->scheduled_time,
            'instructions'     => $this->instructions,
            'start_date'       => $this->start_date?->toDateString(),
            'end_date'         => $this->end_date?->toDateString(),
            'is_active'        => $this->is_active,
        ];
    }
}
