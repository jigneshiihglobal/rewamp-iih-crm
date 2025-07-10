<?php

namespace App\Http\Requests;

use App\Enums\FollowUpStatus;
use App\Enums\FollowUpType;
use App\Helpers\DateHelper;
use App\Models\FollowUp;
use DateTime;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BulkUpdateLeadFollowUpRequest extends FormRequest
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

        $type = $this->route('type') ?? FollowUpType::EMAIL;
        $date_format = DateHelper::FOLLOW_UP_DATE_DATE;
        $time_format = DateHelper::FOLLOW_UP_DATE_HOUR;
        $now = new DateTime("now", new DateTimeZone(Auth::user()->timezone));

        $rules = [
            "lead_follow_ups" => ['required', 'array', 'min:1'],
            "lead_follow_ups.*" => ['required', 'array'],
            "lead_follow_ups.*.follow_up_id" => ['nullable', 'exists:follow_ups,id'],
            "lead_follow_ups.*.follow_up_date"  => ['required', "date_format:{$date_format}"],
            "lead_follow_ups.*.follow_up_time"  => ['required', "date_format:{$time_format}", function ($a, $v, $f) use ($now) {
                $idAttr = str_replace('follow_up_time', 'follow_up_id', $a);
                $dateAttr = str_replace('follow_up_time', 'follow_up_date', $a);
                $dateStr = $this->input($dateAttr);
                $fullTime = $dateStr . " " .  $v;
                $dateObj = date_create_from_format(DateHelper::FOLLOW_UP_DATE, $fullTime, new DateTimeZone(Auth::user()->timezone));
                if (($dateObj < $now) && empty($this->input($idAttr))) {
                    $f("The follow up date/time must be after current date/time.");
                }
                $follow_up = FollowUp::find($this->input($idAttr));
                if (!$follow_up || $follow_up->status !== FollowUpStatus::COMPLETED) {
                    if (($dateObj < $now)) {
                        $f("The follow up date/time must be after current date/time.");
                    }
                }
            }],
        ];

        if ($type === FollowUpType::CALL) {
            $rules["lead_follow_ups.*.sales_person_phone"]  = ['required', 'array', 'min:1'];
            $rules["lead_follow_ups.*.sales_person_phone.*"]  = ['required', 'string', 'min:8', 'max:20', 'regex:/^(\+)?([0-9]+(\s)?)+$/'];
        } else {
            $rules["lead_follow_ups.*.to"] = ['required', 'array', 'min:1'];
            $rules["lead_follow_ups.*.bcc"] = ['nullable', 'array'];
            $rules["lead_follow_ups.*.to.*"] = ['required', 'email'];
            $rules["lead_follow_ups.*.bcc.*"] = ['required', 'email'];
            $rules["lead_follow_ups.*.content"] = ['required'];
            $rules["lead_follow_ups.*.subject"] = ['required', 'string', 'max:125'];
            $rules["lead_follow_ups.*.email_signature_id"] = ['required', function ($attribute, $value, $fail) {
                if(Auth::user()->email_signatures()->where('id', $value)->doesntExist()) {
                    $fail("Please select valid signature");
                }
            }];
            $rules["lead_follow_ups.*.smtp_credential_id"] = ['required', function ($attribute, $value, $fail) {
                if(Auth::user()->smtp_credentials()->where('id', $value)->doesntExist()) {
                    $fail("Please select valid smtp");
                }
            }];
        }

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];
        $attributes["lead_follow_ups.*.follow_up_id"] = 'follow up';
        $attributes["lead_follow_ups.*.follow_up_date"] = 'follow up date';
        $attributes["lead_follow_ups.*.follow_up_time"] = 'follow up time';
        if ($this->type && $this->type === FollowUpType::CALL) {
            $attributes["lead_follow_ups.*.sales_person_phone"] = 'phone numbers';
            $attributes["lead_follow_ups.*.sales_person_phone.*"] = 'phone number';
        } else {
            $attributes["lead_follow_ups.*.to"] = 'to email addresses';
            $attributes["lead_follow_ups.*.to.*"] = 'to email address';
            $attributes["lead_follow_ups.*.bcc"] = 'BCC email addresses';
            $attributes["lead_follow_ups.*.bcc.*"] = 'BCC email address';
            $attributes["lead_follow_ups.*.subject"] = 'subject';
            $attributes["lead_follow_ups.*.content"] = 'content';
            $attributes["lead_follow_ups.*.email_signature_id"] = 'email signature';
            $attributes["lead_follow_ups.*.smtp_credential_id"] = 'smtp';
        }
        return $attributes;
    }
}
