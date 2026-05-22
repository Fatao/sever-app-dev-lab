<?php
declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\PermissionDTO;
use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission('create-permission');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'unique:permissions,name'],
            'slug'        => ['required', 'string', 'unique:permissions,slug', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Return a PermissionDTO from validated request data.
     */
    public function toDTO(): PermissionDTO
    {
        $data = $this->validated();

        return new PermissionDTO(
            id: 0,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            created_at: now()->format('Y-m-d H:i:s'),
            created_by: $this->user()->id,
        );
    }
}