<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'patient_id'       => $this->patient_id,
            'patient_name'     => $this->patient?->full_name,
            'type'             => $this->type,
            'message'          => $this->message,
            'alert_time'       => $this->alert_time?->format('Y-m-d H:i:s'),
            'is_acknowledged'  => $this->is_acknowledged,
        ];
    }
}
