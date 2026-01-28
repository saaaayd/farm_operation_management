<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class StoreBuyerRequest extends FormRequest
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
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'type' => 'required|string|in:individual,business,wholesaler,retailer',
            'status' => 'required|string|in:active,inactive',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
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
            'name.required' => 'Buyer name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'contact_person.max' => 'Contact person name cannot exceed 255 characters.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'phone.required' => 'Phone number is required.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'type.required' => 'Buyer type is required.',
            'type.in' => 'Type must be one of: individual, business, wholesaler, retailer.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: active, inactive.',
            'payment_terms.max' => 'Payment terms cannot exceed 255 characters.',
            'credit_limit.numeric' => 'Credit limit must be a number.',
            'credit_limit.min' => 'Credit limit must be greater than or equal to 0.',
        ];
    }
}