<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMedicationScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medication_name' => ['sometimes', 'string', 'max:255'],
            'dosage'          => ['sometimes', 'string', 'max:100'],
            'frequency'       => ['sometimes', 'string', 'max:50'],
            'scheduled_time'  => ['sometimes', 'date_format:H:i:s,H:i'],
            'instructions'    => ['nullable', 'string'],
            'start_date'      => ['sometimes', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active'       => ['boolean'],
        ];
    }
}
