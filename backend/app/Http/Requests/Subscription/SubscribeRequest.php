<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validPlans = implode(',', array_keys(config('zumi.subscriptions.plans', [])));

        return [
            'plan' => "required|string|in:{$validPlans}",
        ];
    }
}
