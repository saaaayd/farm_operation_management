<?php

namespace App\Http\Requests\Financial;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|in:seeds,fertilizers,pesticides,labor,equipment,utilities,transportation,other',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|in:cash,bank_transfer,check,credit_card',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'related_entity_type' => 'nullable|string|in:field,planting,harvest,task',
            'related_entity_id' => 'nullable|integer',
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
            'description.required' => 'Expense description is required.',
            'description.max' => 'Description cannot exceed 255 characters.',
            'amount.required' => 'Expense amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be greater than or equal to 0.',
            'category.required' => 'Expense category is required.',
            'category.in' => 'Category must be one of: seeds, fertilizers, pesticides, labor, equipment, utilities, transportation, other.',
            'date.required' => 'Expense date is required.',
            'date.date' => 'Date must be a valid date.',
            'payment_method.in' => 'Payment method must be one of: cash, bank_transfer, check, credit_card.',
            'receipt_number.max' => 'Receipt number cannot exceed 255 characters.',
            'related_entity_type.in' => 'Related entity type must be one of: field, planting, harvest, task.',
            'related_entity_id.integer' => 'Related entity ID must be an integer.',
        ];
    }
}