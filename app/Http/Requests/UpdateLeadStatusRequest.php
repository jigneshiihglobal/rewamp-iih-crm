<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadStatusRequest extends FormRequest
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
        $lead_status = $this->route('lead_status');
        $lead_status_id = $lead_status ? $lead_status->id : 'NULL';

        return [
            'title' => "required|unique:lead_statuses,title,{$lead_status_id},id,deleted_at,NULL",
            'css_class' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'title.unique' => 'A status with same title already exists'
        ];
    }
}
