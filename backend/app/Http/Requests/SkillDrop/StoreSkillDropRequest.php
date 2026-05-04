<?php

namespace App\Http\Requests\SkillDrop;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillDropRequest extends FormRequest
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
            'price_drops' => ['required', 'integer', 'min:0'],
            'preview_url' => ['nullable', 'url'],
            'content_url' => ['nullable', 'url'],
        ];
    }
}
