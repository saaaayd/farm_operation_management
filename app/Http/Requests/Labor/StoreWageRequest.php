<?php

namespace App\Http\Requests\Labor;

use Illuminate\Foundation\Http\FormRequest;

class StoreWageRequest extends FormRequest
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
            'laborer_id' => 'required|exists:laborers,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,check',
            'hours_worked' => 'required|numeric|min:0',
            'hourly_rate' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,paid,overdue',
            'notes' => 'nullable|string',
            'related_task_id' => 'nullable|exists:tasks,id',
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
            'laborer_id.required' => 'Laborer selection is required.',
            'laborer_id.exists' => 'Selected laborer does not exist.',
            'amount.required' => 'Wage amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'payment_date.required' => 'Payment date is required.',
            'payment_date.date' => 'Payment date must be a valid date.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Payment method must be one of: cash, bank_transfer, check.',
            'hours_worked.required' => 'Hours worked is required.',
            'hours_worked.numeric' => 'Hours worked must be a number.',
            'hours_worked.min' => 'Hours worked must be greater than or equal to 0.',
            'hourly_rate.required' => 'Hourly rate is required.',
            'hourly_rate.numeric' => 'Hourly rate must be a number.',
            'hourly_rate.min' => 'Hourly rate must be greater than or equal to 0.',
            'status.required' => 'Payment status is required.',
            'status.in' => 'Status must be one of: pending, paid, overdue.',
            'related_task_id.exists' => 'Selected task does not exist.',
        ];
    }
}