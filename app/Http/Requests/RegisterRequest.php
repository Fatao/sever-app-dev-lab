<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:7',
                'regex:/^[A-Z][a-zA-Z]+$/',
                Rule::unique('users', 'username')
                    ->where(fn ($query) =>
                        $query->whereRaw('LOWER(username) = ?', [strtolower($this->username)])
                    ),
            ],

            'email' => [
                'required',
                'email',
                'unique:users,email'
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[^a-zA-Z0-9]/'
            ],

            'c_password' => [
                'required',
                'same:password'
            ],

            'birthday' => [
                'required',
                'date',
                'before:' . now()->subYears(14)->format('Y-m-d')
            ],
        ];
    }
}