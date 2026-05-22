<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password'         => [
                'required',
                'string',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
            ],
            'c_password' => ['required', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'Password must contain at least one digit, one special character, one uppercase and one lowercase letter.',
        ];
    }
}