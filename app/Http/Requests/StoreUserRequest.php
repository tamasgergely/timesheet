<?php

namespace App\Http\Requests;

use App\Rules\Teams;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'email' => 'required|max:255|email',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|between:6,20|confirmed',
            'avatar' => 'nullable|file|image|mimes:jpg,png|max:1024',
            'teams' => ['nullable', new Teams]
        ];
    }
}
