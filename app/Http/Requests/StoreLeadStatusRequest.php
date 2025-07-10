<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadStatusRequest extends FormRequest
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
            'title' => 'required|unique:lead_statuses,title,NULL,id,deleted_at,NULL',
            'css_class' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'title.unique' => 'A status with same title already exists',
        ];
    }
}
