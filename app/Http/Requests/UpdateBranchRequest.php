<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
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
    
        $branchId = $this->route('branch') instanceof \App\Models\Branch
            ? $this->route('branch')->id
            : $this->route('branch');
        return [
            'name'    => ['sometimes', 'required', 'string', 'max:255'],
            'code'    => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')->ignore($branchId),
            ],
            'phone'   => ['nullable', 'string', 'max:50'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'status'  => ['sometimes', 'required', 'string', 'max:20', 'in:active,inactive'],
        ];
    }
}