<?php

namespace App\Http\Requests;

use App\Helpers\DateHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends FormRequest
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
        $emailUniqueIgnore = $this->route('user')
            ? $this->route('user')->id
            : Auth::id();

        return [
            'first_name'    => 'required|string|min:2',
            'last_name'     => 'required|string|min:2',
            'email'         => "required|unique:users,email,{$emailUniqueIgnore},id,deleted_at,NULL",
            'phone'         => 'nullable|string|min:10|max:20|regex:/^(\+)?([0-9]+(\s)?)+$/',
            'dob'           => 'nullable|date_format:' . DateHelper::DOB_DATE_FORMAT,
            'gender'        => 'nullable|in:male,female',
            'address'       => 'nullable',
            'city'          => 'nullable',
            'state'         => 'nullable',
            'country'       => 'nullable',
            'postal'        => 'nullable',
            'timezone'      => 'nullable|timezone'
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'This email is already taken',
            "phone.regex" => "Only number, plus sign and spaces are allowed in a phone number",
            'dob.date_format' => 'Pleae enter a valid date in format of dd/mm/yyyy'
        ];
    }
}
