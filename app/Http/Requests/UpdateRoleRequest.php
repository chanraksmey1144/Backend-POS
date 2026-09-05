<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleId = $this->route('role') instanceof \App\Models\Role
            ? $this->route('role')->id
            : $this->route('role');
        return [
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('roles', 'key')->ignore($roleId),
            ],
            'name'        => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'grant_all'   => ['sometimes', 'boolean'],
        ];
    }
}
