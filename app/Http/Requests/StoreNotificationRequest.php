<?php

namespace App\Http\Requests;

use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreNotificationRequest extends FormRequest
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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'type'    => ['required', 'string', new Enum(NotificationType::class)],
            'title'   => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'link'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
