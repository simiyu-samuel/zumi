<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'username' => [
                'sometimes',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'bio'      => ['nullable', 'string', 'max:1000'],
        ];
    }
}
