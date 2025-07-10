<?php

namespace App\Http\Requests;

use App\Enums\FollowUpType;
use App\Helpers\DateHelper;
use App\Helpers\EncryptionHelper;
use DateTime;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreFollowUpRequest extends FormRequest
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
        $followUpAtFormat = DateHelper::FOLLOW_UP_DATE ?? '';
        $after = new DateTime($this->type == FollowUpType::CALL  ? '+1 hour' :  'now', new DateTimeZone(Auth::user()->timezone));
        $after = date_format($after, $followUpAtFormat);

        return [
            'lead_id' => 'required|exists:leads,id',
            'type' => ['required', Rule::in(FollowUpType::values())],
            'to' => ["required_if:type,$followUpEmail", 'array'],
            'to.*' => ["required_if:type,$followUpEmail", "email"],
            'bcc' => ["required_if:type,$followUpEmail", 'array'],
            'bcc.*' => ["required_if:type,$followUpEmail", "email"],
            'content' => ["required_if:type,$followUpEmail"],
            'subject' => ["required_if:type,$followUpEmail", "nullable", "string", "max:125"],
            'sales_person_phone' => ["required_if:type,$followUpCall", 'array'],
            'sales_person_phone.*' => ["required_if:type,$followUpCall"],
            'follow_up_at' => ["required", "date_format:$followUpAtFormat", "after_or_equal:$after"],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'lead_id' => EncryptionHelper::decrypt($this->lead_id),
        ]);
    }
}
