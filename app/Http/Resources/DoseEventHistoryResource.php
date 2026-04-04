<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoseEventHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'patient_id'             => $this->patient_id,
            'patient_name'           => $this->patient?->full_name,
            'medication_schedule_id' => $this->medication_schedule_id,
            'medication_name'        => $this->medicationSchedule?->medication_name,
            'event_time'             => $this->event_time?->format('Y-m-d H:i:s'),
            'status'                 => $this->status,
            'notes'                  => $this->notes,
        ];
    }
}
