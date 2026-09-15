<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allocations' => ['required', 'array'],
            'allocations.*.vendor_category_id' => ['required', 'exists:vendor_categories,id'],
            'allocations.*.percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'allocations.*.allocated_amount_idr' => ['required', 'integer', 'min:0'],
            'allocations.*.notes' => ['nullable', 'string'],
        ];
    }
}
