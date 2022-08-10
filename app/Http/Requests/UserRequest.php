<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $todayDate = date('Y-m-d');

        return [
            'email' => ['required', 'email', 'unique:users,email,' . $this->route('user.id'), 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'role' => ['required'],
            'user_info' => ['required'],
            'user_info.first_name' => ['nullable', 'string', 'max:255'],
            'user_info.last_name' => ['nullable', 'string', 'max:255'],
            'user_info.middle_name' => ['nullable', 'string', 'max:255'],
            'user_info.mobile_number' => ['nullable', 'regex:/^(\+63)\d{10}$/'],
            'user_info.sex' => ['nullable', 'string', Rule::in(['male', 'female'])],
            'user_info.birthday' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:' . $todayDate],
            'user_info.home_address' => ['nullable', 'string', 'max:255'],
            'user_info.barangay' => ['nullable', 'string', 'max:255'],
            'user_info.city' => ['nullable', 'string', 'max:255'],
            'user_info.region' => ['nullable', 'string', 'max:255']
        ];
    }
}
