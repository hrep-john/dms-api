<?php

namespace App\Http\Requests\ReportBuilder;

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
            'module' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255', 'unique:report_builders,name,' . $this->route('report_builder.id')],
            'format' => ['required', 'json'],
        ];
    }

    public function messages()
    {
        return [];
    }
}
