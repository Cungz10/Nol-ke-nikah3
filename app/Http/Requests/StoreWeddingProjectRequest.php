<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeddingProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->belongsToTeam($this->route('current_team')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'partner_one_name' => ['required', 'string', 'max:255'],
            'partner_two_name' => ['nullable', 'string', 'max:255'],
            'target_date' => ['nullable', 'date', 'after:today'],
            'budget_total' => ['required', 'integer', 'min:0'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'city' => ['required', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:100'],
            'tradition' => ['nullable', 'string', 'max:100'],
        ];
    }
}
