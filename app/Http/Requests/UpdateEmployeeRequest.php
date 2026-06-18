<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmployeeRequest extends FormRequest
{
    private const ALLOWED_KEYS = ['name', 'email', 'isActive'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($this->route('employee')),
            ],
            'isActive' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $unknown = array_diff(array_keys($this->all()), self::ALLOWED_KEYS);
        if (!empty($unknown)) {
            $validator->after(function ($v) {
                $v->errors()->add('payload', 'The request contains unexpected fields.');
            });
        }
    }

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if (array_key_exists('isActive', $data)) {
            $data['is_active'] = $data['isActive'];
            unset($data['isActive']);
        }

        return $data;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }
}
