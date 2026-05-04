<?php

namespace App\Http\Requests\Api;

use App\Enums\ReportReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreReportRequest extends FormRequest
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
            'reported_type' => ['required', 'string', 'in:wave,comment,user'],
            'reported_id'   => ['required', 'string'],
            'reason'        => ['required', new Enum(ReportReason::class)],
            'description'   => ['nullable', 'string', 'max:1000'],
        ];
    }
}
