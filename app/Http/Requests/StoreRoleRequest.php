<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\RoleDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('create-role');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'unique:roles,name'],
            'slug'        => ['required', 'string', 'unique:roles,slug', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Return a RoleDTO from validated request data.
     */
    public function toDTO(): RoleDTO
    {
        $data = $this->validated();

        return new RoleDTO(
            id: 0,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            created_at: now()->format('Y-m-d H:i:s'),
            created_by: $this->user()->id,
        );
    }
}