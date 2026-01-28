<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
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
            'harvest_id' => 'required|exists:harvests,id',
            'buyer_id' => 'required|exists:buyers,id',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,check,credit',
            'payment_status' => 'required|string|in:pending,paid,partial,overdue',
            'delivery_date' => 'nullable|date|after_or_equal:sale_date',
            'delivery_address' => 'nullable|string',
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
            'harvest_id.required' => 'Harvest selection is required.',
            'harvest_id.exists' => 'Selected harvest does not exist.',
            'buyer_id.required' => 'Buyer selection is required.',
            'buyer_id.exists' => 'Selected buyer does not exist.',
            'quantity.required' => 'Sale quantity is required.',
            'quantity.numeric' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity must be greater than or equal to 0.',
            'unit_price.required' => 'Unit price is required.',
            'unit_price.numeric' => 'Unit price must be a number.',
            'unit_price.min' => 'Unit price must be greater than or equal to 0.',
            'total_amount.required' => 'Total amount is required.',
            'total_amount.numeric' => 'Total amount must be a number.',
            'total_amount.min' => 'Total amount must be greater than or equal to 0.',
            'sale_date.required' => 'Sale date is required.',
            'sale_date.date' => 'Sale date must be a valid date.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Payment method must be one of: cash, bank_transfer, check, credit.',
            'payment_status.required' => 'Payment status is required.',
            'payment_status.in' => 'Payment status must be one of: pending, paid, partial, overdue.',
            'delivery_date.date' => 'Delivery date must be a valid date.',
            'delivery_date.after_or_equal' => 'Delivery date must be on or after sale date.',
        ];
    }
}