<?php

namespace App\Http\Requests\StatusRevision;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStatusRevisionRequest extends FormRequest
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
                Rule::unique('status_revisions')->where(function ($query) {
                    return $query->where('status_update_id', $this->status_update_id)
                        ->where('revision_number', $this->revision_number);
                }),
            ],
            'revision_number' => ['required', 'integer', 'min:0', 'max:9'],
            'revision_date' => ['date'],
        ];
    }
}
