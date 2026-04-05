<?php

namespace App\Http\Requests;

use App\Models\MedicationSchedule;
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $patientId    = $this->input('patient_id');
            $scheduleId   = $this->input('medication_schedule_id');

            if ($patientId && $scheduleId) {
                $belongs = MedicationSchedule::where('id', $scheduleId)
                    ->where('patient_id', $patientId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add(
                        'medication_schedule_id',
                        'The selected medication schedule does not belong to the given patient.'
                    );
                }
            }
        });
    }
}
