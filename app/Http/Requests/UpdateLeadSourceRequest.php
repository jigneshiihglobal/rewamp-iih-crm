<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadSourceRequest extends FormRequest
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
        $lead_source = $this->route('lead_source');
        $lead_source_id = $lead_source ? $lead_source->id : 'NULL';

        return [
            'title' => "required|unique:lead_sources,title,{$lead_source_id},id,deleted_at,NULL"
        ];
    }

    public function messages()
    {
        return [
            'title.unique' => 'A source with same title already exists'
        ];
    }
}
