<?php

namespace App\Http\Requests\Transmittal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'document_id' => ['required', 'integer'],
            'image' => ['required']
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'image.mimes' => 'The uploaded file must a JPEG or PNG',
            'image.max' => 'The file must not exceed 10MB'
        ];
    }
}
