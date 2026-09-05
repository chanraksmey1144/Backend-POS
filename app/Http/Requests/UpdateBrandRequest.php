<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
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
        $brandId = $this->route('brand') instanceof \App\Models\Brand
            ? $this->route('brand')->id
            : $this->route('brand');
        return [
            'name'   => ['sometimes', 'required', 'string', 'max:255'],
            'code'   => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('brands', 'code')->ignore($brandId),
            ],
            'status' => ['sometimes', 'required', 'string', 'max:20', 'in:active,inactive'],
        ];
    }
}
