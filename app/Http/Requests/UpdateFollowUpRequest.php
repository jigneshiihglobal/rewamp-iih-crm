<?php

namespace App\Http\Requests;

use App\Enums\FollowUpType;
use App\Helpers\DateHelper;
use DateTime;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateFollowUpRequest extends FormRequest
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

        $followUpEmail = FollowUpType::EMAIL;
        $followUpCall = FollowUpType::CALL;
        $date_format = DateHelper::FOLLOW_UP_DATE_DATE ?? '';
        $time_format = DateHelper::FOLLOW_UP_DATE_HOUR ?? '';
        $after = new DateTime($this->type == FollowUpType::CALL  ? '+1 hour' :  'now', new DateTimeZone(Auth::user()->timezone));

        return [
            'type' => ['required', Rule::in(FollowUpType::values())],
            'to' => ["required_if:type,$followUpEmail", 'array'],
            'to.*' => ["required_if:type,$followUpEmail", "email"],
            'bcc' => ["nullable", 'array'],
            'bcc.*' => ["required_if:type,$followUpEmail", "email"],
            'content' => ["required_if:type,$followUpEmail"],
            'subject' => ["required_if:type,$followUpEmail", "nullable", "string", "max:125"],
            'sales_person_phone' => ["required_if:type,$followUpCall", 'array'],
            'sales_person_phone.*' => ["required_if:type,$followUpCall"],
            'email_signature_id' => ["required_if:type,$followUpEmail", function ($a, $v, $f) {
                if(Auth::user()->email_signatures()->where('id', $v)->doesntExist()) $f("Please select valid email signature");
            }],
            // 'smtp_credential_id' => ["required_if:type,$followUpEmail", "nullable"],
            'smtp_credential_id' => ["required_if:type,$followUpEmail", function ($a, $v, $f) {
                if(Auth::user()->smtp_credentials()->where('id', $v)->doesntExist()) $f("Please select valid SMTP");
            }],
            "follow_up_date"  => ['required', "date_format:{$date_format}"],
            "follow_up_time"  => [
                'required',
                "date_format:{$time_format}",
                function ($a, $v, $f) use ($after, $date_format, $time_format) {
                    $dateObj = date_create_from_format(
                        DateHelper::FOLLOW_UP_DATE,
                        $this->follow_up_date . " " .  $v,
                        new DateTimeZone(Auth::user()->timezone)
                    );
                    if ($dateObj < $after) {
                        $f("The follow up date & time must be after " . date_format($after, $date_format . " " . $time_format));
                    }
                }
            ],
        ];
    }
}
