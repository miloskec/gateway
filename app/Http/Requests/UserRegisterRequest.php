<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //return false;
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
            'username' => 'required|string',
            'full_name' => 'required|string',
            'email' => 'required|email',
            'is_admin' => 'boolean', // 'is_admin' is optional and defaults to 'false
            'password' => [
                'required',
                'string',
                'min:8',            // Must be at least 8 characters
                'confirmed',        // Must match the password_confirmation field
                'regex:/[a-z]/',    // Must contain at least one lowercase letter
                'regex:/[A-Z]/',    // Must contain at least one uppercase letter
                'regex:/[0-9]/',    // Must contain at least one digit
                'regex:/[@$!%*#?&]/', // Must contain a special character
            ],
        ];
    }
}
