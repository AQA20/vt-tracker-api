<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadStatusPdfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedFields = [
            'tech_sub_status_pdf',
            'sample_status_pdf',
            'layout_status_pdf',
            'car_m_dwg_status_pdf',
            'cop_dwg_status_pdf',
            'landing_dwg_status_pdf',
        ];

        // The 'field' is a route parameter, so we authorize it implicitly or check it here?
        // Request validation usually validates body.
        // Route parameters can be validated if we merge them to input or access checks.
        // We will validate 'file' here. The controller checks field whitelist or we use route validation.
        // The prompt says: "UploadStatusPdfRequest (validates {field} whitelist + file pdf + max size)"

        $this->merge(['field_name' => $this->route('field')]);

        return [
            'field_name' => ['required', Rule::in($allowedFields)],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ];
    }
}
