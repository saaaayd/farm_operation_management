<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|in:seeds,fertilizers,pesticides,equipment,tools,other',
            'unit' => 'required|string|in:kg,grams,pounds,liters,gallons,pieces,packets',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
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
            'name.required' => 'Item name is required.',
            'name.max' => 'Item name cannot exceed 255 characters.',
            'category.required' => 'Item category is required.',
            'category.in' => 'Category must be one of: seeds, fertilizers, pesticides, equipment, tools, other.',
            'unit.required' => 'Unit is required.',
            'unit.in' => 'Unit must be one of: kg, grams, pounds, liters, gallons, pieces, packets.',
            'current_stock.required' => 'Current stock is required.',
            'current_stock.numeric' => 'Current stock must be a number.',
            'current_stock.min' => 'Current stock must be greater than or equal to 0.',
            'minimum_stock.required' => 'Minimum stock is required.',
            'minimum_stock.numeric' => 'Minimum stock must be a number.',
            'minimum_stock.min' => 'Minimum stock must be greater than or equal to 0.',
            'unit_price.numeric' => 'Unit price must be a number.',
            'unit_price.min' => 'Unit price must be greater than or equal to 0.',
            'supplier.max' => 'Supplier name cannot exceed 255 characters.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'expiry_date.date' => 'Expiry date must be a valid date.',
        ];
    }
}