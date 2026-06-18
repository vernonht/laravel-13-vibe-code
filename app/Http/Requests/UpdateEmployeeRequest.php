<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($this->route('employee')),
            ],
            'isActive' => ['sometimes', 'required', 'boolean'],
        ];
    }

    protected function passedValidation(): void
    {
        if ($this->has('isActive')) {
            $this->merge(['is_active' => $this->boolean('isActive')]);
            $this->offsetUnset('isActive');
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }
}
