<?php

namespace App\Http\Requests\Wave;

use App\Models\Wave;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'visibility'  => ['sometimes', Rule::in([Wave::VISIBILITY_PUBLIC, Wave::VISIBILITY_PRIVATE, Wave::VISIBILITY_GATED])],
            'gated_drops' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
