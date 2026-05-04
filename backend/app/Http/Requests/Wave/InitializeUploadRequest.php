<?php

namespace App\Http\Requests\Wave;

use Illuminate\Foundation\Http\FormRequest;

class InitializeUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'size_bytes' => 'required|integer|max:104857600', // 100MB max
            'title'      => 'nullable|string|max:255',
        ];
    }
}
