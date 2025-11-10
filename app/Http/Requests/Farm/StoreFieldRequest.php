<?php

namespace App\Http\Requests\Farm;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldRequest extends FormRequest
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
            'location' => 'required|array',
            'location.lat' => 'required|numeric|between:-90,90',
            'location.lon' => 'required|numeric|between:-180,180',
            'location.address' => 'required|string|max:255',
            'soil_type' => 'required|string|max:255',
            'size' => 'required|numeric|min:0',
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
            'location.required' => 'Field location is required.',
            'location.lat.required' => 'Latitude is required.',
            'location.lat.numeric' => 'Latitude must be a number.',
            'location.lat.between' => 'Latitude must be between -90 and 90.',
            'location.lon.required' => 'Longitude is required.',
            'location.lon.numeric' => 'Longitude must be a number.',
            'location.lon.between' => 'Longitude must be between -180 and 180.',
            'soil_type.required' => 'Soil type is required.',
            'size.required' => 'Field size is required.',
            'size.numeric' => 'Field size must be a number.',
            'size.min' => 'Field size must be greater than 0.',
        ];
    }
}