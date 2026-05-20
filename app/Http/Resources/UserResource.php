<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'push_token' => $this->push_token,
            'patient_id' => $this->when(
                $this->role === 'patient',
                fn () => $this->relationLoaded('patient') ? $this->patient?->id : null
            ),
            'patient' => $this->when(
                $this->role === 'patient' && $this->relationLoaded('patient') && $this->patient,
                fn () => new PatientResource($this->patient)
            ),
        ];
    }
}
