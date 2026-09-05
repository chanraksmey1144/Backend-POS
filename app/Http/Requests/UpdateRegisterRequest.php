<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegisterRequest extends FormRequest
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
        $registerId = $this->route('register') instanceof \App\Models\Register
            ? $this->route('register')->id
            : $this->route('register');
        return [
            'branch_id' => ['sometimes', 'required', 'integer', 'exists:branches,id'],
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'code'      => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('registers', 'code')->ignore($registerId),
            ],
            'status'    => ['sometimes', 'required', 'string', 'max:20', 'in:active,inactive'],
        ];
    }
}
