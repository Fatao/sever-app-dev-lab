<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\RoleDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        $permission = 'create-role';
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
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name'],
            'slug'        => ['required', 'string', 'max:255', 'unique:roles,slug', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Convert request to RoleDTO.
     */
    public function toDTO(): RoleDTO
    {
        $data = $this->validated();

        return new RoleDTO(
            id: 0,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            created_at: null,
            created_by: null,
        );
    }
}