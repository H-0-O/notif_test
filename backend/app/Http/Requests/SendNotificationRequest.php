<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNotificationRequest extends FormRequest
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
            'types' => ['array'],
            'types.*' => Rule::in(['email', 'sms']),
            'userIds' => ['array','required'],
            'userIds.*' => ['integer','required'],
            'title' => ['string','required'],
            'body' => ['string','required'],
        ];
    }

    public function messages(): array
    {
        return [
            '*.type.in' => 'Notification type must be either email or sms.',
            '*.user_id.required' => 'User ID is required for each notification.',
        ];
    }
}
