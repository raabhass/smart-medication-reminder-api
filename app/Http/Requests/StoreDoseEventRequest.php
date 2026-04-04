<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoseEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'              => ['required', 'exists:patients,id'],
            'medication_schedule_id'  => ['required', 'exists:medication_schedules,id'],
            'status'                  => ['required', 'in:taken,missed,skipped'],
            'event_time'              => ['required', 'date'],
            'notes'                   => ['nullable', 'string'],
        ];
    }
}
