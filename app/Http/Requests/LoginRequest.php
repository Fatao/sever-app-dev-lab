<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'username' => [
                'required',
                'string',
                'min:7',
                'regex:/^[A-Z][a-zA-Z]+$/'  // Starts with uppercase, then letters only
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[0-9]/',           // Contains number
                'regex:/[A-Z]/',           // Contains uppercase
                'regex:/[a-z]/',           // Contains lowercase
                'regex:/[^a-zA-Z0-9]/'     // Contains special char
            ],
        ];
    }
}
