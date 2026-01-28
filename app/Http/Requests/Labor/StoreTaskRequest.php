<?php

namespace App\Http\Requests\Labor;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'laborer_id' => 'required|exists:laborers,id',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'due_date' => 'required|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'hours_worked' => 'nullable|numeric|min:0',
            'related_entity_type' => 'nullable|string|in:field,planting,harvest',
            'related_entity_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'laborer_id.required' => 'Laborer selection is required.',
            'laborer_id.exists' => 'Selected laborer does not exist.',
            'priority.required' => 'Task priority is required.',
            'priority.in' => 'Priority must be one of: low, medium, high, urgent.',
            'status.required' => 'Task status is required.',
            'status.in' => 'Status must be one of: pending, in_progress, completed, cancelled.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'estimated_hours.numeric' => 'Estimated hours must be a number.',
            'estimated_hours.min' => 'Estimated hours must be greater than or equal to 0.',
            'hours_worked.numeric' => 'Hours worked must be a number.',
            'hours_worked.min' => 'Hours worked must be greater than or equal to 0.',
            'related_entity_type.in' => 'Related entity type must be one of: field, planting, harvest.',
            'related_entity_id.integer' => 'Related entity ID must be an integer.',
        ];
    }
}