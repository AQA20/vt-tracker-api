<?php

namespace App\Http\Requests\DeliveryGroupItem;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeliveryGroupItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_module_content_id' => ['required', 'uuid', 'exists:delivery_module_contents,id'],
            'remarks' => ['nullable', 'string'],
            'package_type' => ['required', Rule::in(['Standard Packing', 'Sea Packing', 'Bark Free Packing'])],
            'special_delivery_address' => ['nullable', 'string'],
        ];
    }
}
