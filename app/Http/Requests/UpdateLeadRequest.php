<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
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
            "firstname"         => "required",
            "lastname"          => "required",
            "mobile"            => "nullable",
            "email.*"             => "nullable|email",
            "requirement"       => "nullable",
            "prj_budget"        => "nullable",
            "currency_id"       => "nullable|required_with:prj_budget",
            "lead_source_id"    => "required|exists:lead_sources,id",
            "lead_status_id"    => "required|exists:lead_statuses,id",
            "country_id"        => "nullable|exists:countries,id",
            "assigned_to"       => "required|exists:users,id",
            'attachments'       => 'nullable|array|max:5',
            'attachments.*'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,gif,bmp,webp,svg,tiff|max:1024',
        ];
    }
}
