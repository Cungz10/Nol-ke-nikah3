<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'budget_allocation_id' => ['nullable', 'exists:budget_allocations,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount_idr' => ['required', 'integer', 'min:1'],
            'payment_type' => ['required', 'string', 'in:dp,installment,full,manual_expense'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
