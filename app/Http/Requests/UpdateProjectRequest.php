<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'client' => 'required|exists:clients,id',
            'name' => [
                'required',
                'max:255',
            ],
            'description' => 'string|nullable',
            'active' => 'boolean'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'client.required' => 'Client is required.',
            'client.exists' => 'Client does not exists.',
            'name.unique' => 'Project name has already been taken.',
            'active.boolean' => 'Active field is invalid'
        ];
    }

}
