<?php

namespace App\Http\Requests;

use App\Enums\AuditAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAuditLogRequest extends FormRequest
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
        return [
            'user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'action'      => ['required', 'string', new Enum(AuditAction::class)],
            'module'      => ['required', 'string', 'max:50'],
            'record'      => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
