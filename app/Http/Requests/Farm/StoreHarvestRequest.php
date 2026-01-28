<?php

namespace App\Http\Requests\Farm;

use Illuminate\Foundation\Http\FormRequest;

class StoreHarvestRequest extends FormRequest
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
            'planting_id' => 'required|exists:plantings,id',
            'harvest_date' => 'required|date',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|in:kg,grams,pounds,bushels,tons',
            'quality_grade' => 'nullable|string|in:A,B,C,D',
            'price_per_unit' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
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
            'planting_id.required' => 'Planting selection is required.',
            'planting_id.exists' => 'Selected planting does not exist.',
            'harvest_date.required' => 'Harvest date is required.',
            'harvest_date.date' => 'Harvest date must be a valid date.',
            'quantity.required' => 'Harvest quantity is required.',
            'quantity.numeric' => 'Harvest quantity must be a number.',
            'quantity.min' => 'Harvest quantity must be greater than or equal to 0.',
            'unit.required' => 'Unit is required.',
            'unit.in' => 'Unit must be one of: kg, grams, pounds, bushels, tons.',
            'quality_grade.in' => 'Quality grade must be one of: A, B, C, D.',
            'price_per_unit.numeric' => 'Price per unit must be a number.',
            'price_per_unit.min' => 'Price per unit must be greater than or equal to 0.',
            'total_value.numeric' => 'Total value must be a number.',
            'total_value.min' => 'Total value must be greater than or equal to 0.',
        ];
    }
}