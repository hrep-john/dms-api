<?php

namespace App\Http\Requests\ReportBuilder;

use App\Rules\UniqueTenantReportBuilder;
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
        $id = $this->route('report_builder.id');

        return [
            'module' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255', new UniqueTenantReportBuilder($id)],
            'format' => ['required', 'json'],
        ];
    }

    public function messages()
    {
        return [];
    }
}
