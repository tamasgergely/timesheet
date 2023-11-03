<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Rules\Teams;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'max:255',
                'email',
                Rule::unique('users')->ignore($this->user->id),
            ],
            'role_id' => [
                Rule::requiredIf(Auth::user()->role_id === Role::ADMIN),
                'exists:roles,id'
            ],
            'password' => 'nullable|between:6,20|confirmed',
            'teams' => ['nullable', new Teams]
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
            'role_id.required' => 'The role field is required.',
        ];
    }    
}
