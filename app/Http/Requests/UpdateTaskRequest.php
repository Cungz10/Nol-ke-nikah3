<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'timeline_phase_id' => ['sometimes', 'required', 'exists:timeline_phases,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_critical' => ['sometimes', 'boolean'],
            'due_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', 'string', 'in:todo,in_progress,completed'],
        ];
    }
}
