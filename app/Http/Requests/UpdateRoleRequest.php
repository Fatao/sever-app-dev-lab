<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\RoleDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('update-role');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name'        => ['required', 'string', "unique:roles,name,{$roleId}"],
            'slug'        => ['required', 'string', "unique:roles,slug,{$roleId}", 'regex:/^[a-zA-Z0-9_-]+$/'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Return a RoleDTO from validated request data.
     */
    public function toDTO(): RoleDTO
    {
        $data = $this->validated();
        $role = $this->route('role');

        return new RoleDTO(
            id: $role->id,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            created_at: $role->created_at->format('Y-m-d H:i:s'),
            created_by: $role->created_by,
        );
    }
}