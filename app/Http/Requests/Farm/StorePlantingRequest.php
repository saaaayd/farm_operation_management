<?php

namespace App\Http\Requests\Farm;

use Illuminate\Foundation\Http\FormRequest;

class StorePlantingRequest extends FormRequest
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
            'field_id' => 'required|exists:fields,id',
            'crop_name' => 'required|string|max:255',
            'variety' => 'nullable|string|max:255',
            'planting_date' => 'required|date',
            'expected_harvest_date' => 'nullable|date|after:planting_date',
            'seed_quantity' => 'required|numeric|min:0',
            'seed_unit' => 'required|string|in:kg,grams,pounds,seeds',
            'spacing' => 'nullable|array',
            'spacing.row' => 'nullable|numeric|min:0',
            'spacing.plant' => 'nullable|numeric|min:0',
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
            'field_id.required' => 'Field selection is required.',
            'field_id.exists' => 'Selected field does not exist.',
            'crop_name.required' => 'Crop name is required.',
            'planting_date.required' => 'Planting date is required.',
            'planting_date.date' => 'Planting date must be a valid date.',
            'expected_harvest_date.date' => 'Expected harvest date must be a valid date.',
            'expected_harvest_date.after' => 'Expected harvest date must be after planting date.',
            'seed_quantity.required' => 'Seed quantity is required.',
            'seed_quantity.numeric' => 'Seed quantity must be a number.',
            'seed_quantity.min' => 'Seed quantity must be greater than or equal to 0.',
            'seed_unit.required' => 'Seed unit is required.',
            'seed_unit.in' => 'Seed unit must be one of: kg, grams, pounds, seeds.',
        ];
    }
}