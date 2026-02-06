<?php

namespace App\Http\Requests\StatusApproval;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusApprovalRequest extends FormRequest
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
            'approved_at' => ['date'],
        ];
    }
}
