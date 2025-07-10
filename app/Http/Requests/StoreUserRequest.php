<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user() && Auth::user()->hasRole(['Admin', 'Superadmin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required|in:male,female,other',
            'email' => "required|email|unique:users,email,NULL,id,deleted_at,NULL",
            'phone' => 'nullable|string|min:10|max:20|regex:/^(\+)?([0-9]+(\s)?)+$/',
            'role' => "required|exists:roles,name",
            'timezone' => 'required|timezone',
            'workspaces' => [Rule::requiredIf(function () {
                return $this->user() && $this->user()->hasRole(['Superadmin']);
            }), 'array', 'min:1'],
            'workspaces.*' => [Rule::requiredIf(function ()
            {
                return $this->user() && $this->user()->hasRole(['Superadmin']);
            }), 'exists:workspaces,id'],
            'is_active' => 'boolean'
        ];
    }

    public function messages()
    {
        return [
            "phone.regex" => "Only number, plus sign and spaces are allowed in a phone number"
        ];
    }
}
