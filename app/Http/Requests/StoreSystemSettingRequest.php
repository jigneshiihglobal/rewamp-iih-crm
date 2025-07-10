<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSystemSettingRequest extends FormRequest
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
            'whitelisted_ips'           => ['required', 'array', 'min:1'],
            'whitelisted_ips.*'         => ['required', 'ipv4'],
            'login_mail_recipients'     => ['required', 'array', 'min:1'],
            'login_mail_recipients.*'   => ['required', 'email'],
        ];
    }

    public function attributes()
    {
        return [
            'whitelisted_ips'           => 'Whitelisted IPs',
            'login_mail_recipients'     => 'Login mail recipients',
        ];
    }
}
