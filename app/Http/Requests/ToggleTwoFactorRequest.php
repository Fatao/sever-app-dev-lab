<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'password' => ['required', 'string'],
            'enable'   => ['required', 'boolean'],
        ];

        // When disabling 2FA, require current 2FA code
        if ($this->input('enable') === false || $this->input('enable') === 'false') {
            $rules['code'] = ['required', 'string', 'digits:6'];
        }

        return $rules;
    }
}