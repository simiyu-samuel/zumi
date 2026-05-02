<?php

namespace App\Http\Requests\Wave;

use App\Models\Wave;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stream_id'   => ['required', 'string'],
            'visibility'  => ['nullable', Rule::in([Wave::VISIBILITY_PUBLIC, Wave::VISIBILITY_PRIVATE, Wave::VISIBILITY_GATED])],
            'gated_drops' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
