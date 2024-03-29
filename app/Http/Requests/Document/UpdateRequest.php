<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'user_defined_field' => ['nullable'],
            'allow_user_access' => ['required', 'integer'],
            'user_access' => ['array'],
            'user_access.*' => ['required', 'exists:users,id'],
        ];
    }
}
