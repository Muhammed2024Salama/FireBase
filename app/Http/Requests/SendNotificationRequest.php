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
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'tokens' => 'nullable|array',
            'tokens.*' => 'string|max:500',
            'data' => 'nullable|array',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasUserIds = $this->has('user_ids') && !empty($this->user_ids);
            $hasTokens = $this->has('tokens') && !empty($this->tokens);
            
            if (!$hasUserIds && !$hasTokens) {
                $validator->errors()->add('user_ids', 'Either user_ids or tokens must be provided and not empty');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Notification title is required',
            'title.string' => 'Notification title must be a string',
            'title.max' => 'Notification title must not exceed 255 characters',
            'body.required' => 'Notification body is required',
            'body.string' => 'Notification body must be a string',
            'body.max' => 'Notification body must not exceed 1000 characters',
            'user_ids.array' => 'User IDs must be an array',
            'user_ids.*.integer' => 'Each user ID must be an integer',
            'user_ids.*.exists' => 'One or more user IDs do not exist',
            'tokens.array' => 'Tokens must be an array',
            'tokens.*.string' => 'Each token must be a string',
            'tokens.*.max' => 'Each token must not exceed 500 characters',
            'data.array' => 'Data must be an array',
        ];
    }
}

