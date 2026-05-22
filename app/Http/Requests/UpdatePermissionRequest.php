<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\PermissionDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('update-permission');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id;

        return [
            'name'        => ['required', 'string', "unique:permissions,name,{$permissionId}"],
            'slug'        => ['required', 'string', "unique:permissions,slug,{$permissionId}", 'regex:/^[a-zA-Z0-9_-]+$/'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Return a PermissionDTO from validated request data.
     */
    public function toDTO(): PermissionDTO
    {
        $data = $this->validated();
        $permission = $this->route('permission');

        return new PermissionDTO(
            id: $permission->id,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            created_at: $permission->created_at->format('Y-m-d H:i:s'),
            created_by: $permission->created_by,
        );
    }
}