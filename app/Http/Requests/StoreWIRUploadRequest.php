<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWIRUploadRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpeg,png,webp|max:5120', // 5MB
            'progress_group' => 'required|in:installation,commissioning',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A WIR image file is required',
            'file.file' => 'The file must be a valid file',
            'file.mimes' => 'The file must be a jpeg, png, or webp image',
            'file.max' => 'The file size must not exceed 5MB',
            'progress_group.required' => 'Progress group is required',
            'progress_group.in' => 'Progress group must be either installation or commissioning',
        ];
    }
}
