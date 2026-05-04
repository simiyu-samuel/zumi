<?php

namespace App\Http\Requests\Circle;

use App\Enums\CircleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCircleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'type'                => ['nullable', new Enum(CircleType::class)],
            'avatar'              => 'nullable|string',
            'cover_image'         => 'nullable|string',
            'monthly_drops_price' => 'nullable|integer|min:1',
        ];
    }
}
