<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfile extends FormRequest
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
            'email' => [
                'nullable',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (User::where('email', $value)->where('id', '!=',$this->user()->id)->count() > 0) {
                        $fail('The email has already been taken.');
                    }
                }
            ],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'regex:/^(09)\d{9}$/'],
            'sex' => ['nullable', 'string', 'in:male,female'],
            'birthday' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:' . $todayDate],
            'home_address' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
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
            'mobile_number.regex' => 'The mobile number should follow this format: 09XXXXXXXXX.',
            'sex.in' => 'Valid sex values are `male` and `female`'
        ];
    }
}
