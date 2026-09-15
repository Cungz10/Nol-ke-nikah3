<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:general,legal_kua,legal_church,vendor_contract,invoice,rundown,other'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
