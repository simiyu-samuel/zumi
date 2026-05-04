<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class CompleteOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'interests'          => ['nullable', 'array'],
            'interests.*'        => ['string', 'max:50'],
            'suggested_follows'  => ['nullable', 'array'],
            'suggested_follows.*' => ['exists:users,id'],
        ];
    }
}
