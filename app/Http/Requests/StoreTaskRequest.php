<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'timeline_phase_id' => ['required', 'exists:timeline_phases,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_critical' => ['boolean'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:todo,in_progress,completed'],
        ];
    }
}
