<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'age'       => ['required', 'integer', 'min:0', 'max:130'],
            'gender'    => ['nullable', 'in:male,female,other'],
            'status'    => ['required', 'in:stable,needs_attention'],
            'notes'     => ['nullable', 'string'],
        ];
    }
}
