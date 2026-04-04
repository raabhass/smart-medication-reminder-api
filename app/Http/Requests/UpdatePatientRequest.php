<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'string', 'max:255'],
            'age'       => ['sometimes', 'integer', 'min:0', 'max:130'],
            'gender'    => ['nullable', 'in:male,female,other'],
            'status'    => ['sometimes', 'in:stable,needs_attention'],
            'notes'     => ['nullable', 'string'],
        ];
    }
}
