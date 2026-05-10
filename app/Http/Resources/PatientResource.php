<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'age' => $this->age,
            'gender' => $this->gender,
            'role_label' => 'Patient',
            'status' => $this->status,
            'notes' => $this->notes,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relationship' => $this->emergency_contact_relationship,
            'allergies' => $this->allergies,
            'medical_notes' => $this->medical_notes,
            'total_medications' => $this->when(
                array_key_exists('total_medications', $this->resource->getAttributes()),
                fn () => (int) $this->total_medications
            ),
            'adherence_rate' => $this->when(
                array_key_exists('total_dose_events', $this->resource->getAttributes())
                    && array_key_exists('taken_dose_events', $this->resource->getAttributes()),
                fn () => $this->total_dose_events > 0
                    ? round(($this->taken_dose_events / $this->total_dose_events) * 100, 2)
                    : null
            ),
            'last_taken_at' => $this->when(
                array_key_exists('last_taken_at', $this->resource->getAttributes()),
                fn () => $this->last_taken_at
            ),
            'created_by_user_id' => $this->created_by_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
