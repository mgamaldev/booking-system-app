<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'resource_id' => ['required', 'integer', 'exists:resources,id'],
            'slot_id' => ['required', 'integer', 'exists:slots,id'],
            'status' => ['required', Rule::in(['pending', 'confirmed', 'canceled'])],
            'type' => ['sometimes', 'nullable', Rule::in(['one-on-one', 'recurring', 'group'])],
            'max_participants' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'recurrence_rule' => ['sometimes', 'nullable', Rule::in(['weekly', 'biweekly', 'monthly'])],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
