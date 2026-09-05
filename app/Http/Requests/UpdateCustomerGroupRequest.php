<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerGroupRequest extends FormRequest
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
        $groupId = $this->route('customer_group') instanceof \App\Models\CustomerGroup
            ? $this->route('customer_group')->id
            : $this->route('customer_group');
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('customer_groups', 'name')->ignore($groupId),
            ],
            'discount_percent' => ['sometimes', 'numeric', 'between:0,100'],
        ];
    }
}
