<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'full_name'           => $this->full_name,
            'age'                 => $this->age,
            'gender'              => $this->gender,
            'role_label'          => 'Patient',
            'status'              => $this->status,
            'notes'               => $this->notes,
            'created_by_user_id'  => $this->created_by_user_id,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
