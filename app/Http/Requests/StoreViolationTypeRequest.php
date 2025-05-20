<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreViolationTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all authenticated users
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'violation_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'severity' => 'required|string|in:Low,Medium,High,Very High',
            'offense' => 'required|string|in:1st,2nd,3rd',
            'penalty' => 'required|string|in:W,VW,WW,Pro,Exp'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'violation_name.required' => 'The violation name field is required.',
            'category.required' => 'The category field is required.',
            'severity.required' => 'The severity field is required.',
            'offense.required' => 'The offense field is required.',
            'penalty.required' => 'The penalty field is required.',
        ];
    }
}
