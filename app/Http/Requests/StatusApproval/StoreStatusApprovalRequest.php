<?php

namespace App\Http\Requests\StatusApproval;

use App\Enums\ApprovalCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreStatusApprovalRequest extends FormRequest
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
            'status_update_id' => [
                'required',
                'uuid',
                'exists:status_updates,id',
                Rule::unique('status_approvals')->where(function ($query) {
                    return $query->where('status_update_id', $this->status_update_id)
                        ->where('approval_code', $this->approval_code);
                }),
            ],
            'approval_code' => ['required', new Enum(ApprovalCode::class)],
            'approved_at' => ['date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
