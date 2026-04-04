<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicationScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage'          => ['required', 'string', 'max:100'],
            'frequency'       => ['required', 'string', 'max:50'],
            'scheduled_time'  => ['required', 'date_format:H:i:s,H:i'],
            'instructions'    => ['nullable', 'string'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active'       => ['boolean'],
        ];
    }
}
