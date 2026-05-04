<?php

namespace App\Http\Requests\GatedRoom;

use Illuminate\Foundation\Http\FormRequest;

class StoreGatedRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'entry_fee_drops' => ['required', 'integer', 'min:0'],
            'scheduled_at'    => ['nullable', 'date', 'after:now'],
        ];
    }
}
