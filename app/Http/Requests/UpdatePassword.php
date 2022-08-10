<?php

namespace App\Http\Requests;

use App\Rules\SameAsCurrentPassword;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePassword extends FormRequest
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
            'old_password' => ['required', 'string', new SameAsCurrentPassword()],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed']
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
            'old_password.is_current_password' => 'The old password field is incorrect.',
        ];
    }
}
