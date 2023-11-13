<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'required|exists:projects,id',
            'description' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'client_id.required' => 'The client field is required.',
            'project_id.required' => 'The project field is required.',
            'description.required' => 'The description field is required.',
        ];
    }    
}
