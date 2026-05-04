<?php

namespace App\Http\Requests\Drops;

use Illuminate\Foundation\Http\FormRequest;

class GiftDropsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id'    => ['required', 'uuid', 'exists:users,id'],
            'amount'         => ['required', 'integer', 'min:1'],
            'reference_type' => ['nullable', 'string'],
            'reference_id'   => ['nullable', 'uuid'],
        ];
    }
}
