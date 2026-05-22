<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;

class AttachUserRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        $permission = 'update-user';   // or 'attach-role' if you prefer
        if (!$this->user()->hasPermission($permission)) {
            throw new AuthorizationException("Access denied. Required permission: {$permission}");
        }
        return true;
    }

    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
                Rule::unique('role_user', 'role_id')
                    ->where('user_id', $userId)
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}