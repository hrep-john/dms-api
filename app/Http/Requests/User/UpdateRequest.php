<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

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
        $todayDate = date('Y-m-d');

        return [
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $this->route('user.id'), 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $this->route('user.id'), 'max:255'],
            'roles' => ['required', 'exists:roles,name'],
            'user_info' => ['required'],
            'user_info.tenant_id' => ['required', 'integer'],
            'user_info.first_name' => ['required', 'string', 'max:255'],
            'user_info.last_name' => ['required', 'string', 'max:255'],
            'user_info.middle_name' => ['nullable', 'string', 'max:255'],
            'user_info.mobile_number' => ['nullable', 'regex:/^(09)\d{9}$/'],
            'user_info.sex' => ['nullable', 'string', Rule::in(['male', 'female'])],
            'user_info.birthday' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:' . $todayDate],
            'user_info.home_address' => ['nullable', 'string', 'max:255'],
            'user_info.barangay' => ['nullable', 'string', 'max:255'],
            'user_info.city' => ['nullable', 'string', 'max:255'],
            'user_info.region' => ['nullable', 'string', 'max:255']
        ];
    }

    public function messages()
    {
        return [
            'user_info.tenant_id.required' => 'The tenant name field is required.',
            'user_info.tenant_id.integer' => 'The tenant name field should be in numeric.',
            'user_info.first_name.required' => 'The first name field is required.',
            'user_info.first_name.string' => 'The first name field should be in string.',
            'user_info.first_name.max' => 'The first name field not be greater than 255 characters.',
            'user_info.last_name.required' => 'The last name field is required.',
            'user_info.last_name.string' => 'The last name field should be in string.',
            'user_info.last_name.max' => 'The first name field not be greater than 255 characters.',
            'user_info.mobile_number.regex' => 'The mobile number should follow this format: 09XXXXXXXXX.',
            'user_info.sex.in' => 'Valid sex values are `male` and `female`'
        ];
    }
}
