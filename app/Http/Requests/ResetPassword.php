<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPassword extends FormRequest
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
            'username' => ['required', 'string', 'exists:users,username'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:6', 'max:255'],
            "password_confirmation" => ['required', 'min:6', 'max:255'],
        ];
    }
}
