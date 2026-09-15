<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_category_id' => ['sometimes', 'required', 'exists:vendor_categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'quote_amount_idr' => ['nullable', 'integer', 'min:0'],
            'deal_amount_idr' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'string', 'in:research,negotiation,booked,dp_paid,fully_paid,cancelled'],
            'notes' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ];
    }
}
