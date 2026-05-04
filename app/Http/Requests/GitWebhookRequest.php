<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GitWebhookRequest extends FormRequest
{
    /**
     * Everyone can access the webhook — auth is handled via secret key.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the webhook request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'secret_key' => ['required', 'string'],
        ];
    }
}