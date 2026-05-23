<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogRequestIndexRequest extends FormRequest
{
    /**
     * All authenticated users can access (permission checked in controller).
     */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'page'              => ['integer', 'min:1'],
            'count'             => ['integer', 'min:1', 'max:100'],
            'sortBy'            => ['array'],
            'sortBy.*.key'      => ['required_with:sortBy', 'string', 'in:id,called_at,response_status,user_id,ip_address,controller_path'],
            'sortBy.*.order'    => ['required_with:sortBy', 'string', 'in:asc,desc'],
            'filter'            => ['array'],
            'filter.*.key'      => ['required_with:filter', 'string', 'in:user_id,response_status,ip_address,user_agent,controller_path'],
            'filter.*.value'    => ['required_with:filter', 'string'],
        ];
    }
}