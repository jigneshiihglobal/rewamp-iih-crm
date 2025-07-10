<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Helpers\SlackHelper;
use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Country;
use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LeadController extends Controller
{
    public function getInTouch(Request $request)
    {
        try {
            $input = $request->all();
            $workspace_id = 1;
            if (isset($input['page_url']) && $input['page_url'] != null) {
                $firstname = $input['firstname'] ?? '';
                $firstname_arr = explode(' ', $firstname, 2);
                $firstname = $firstname_arr[0] ?? '';
                $lastname = $firstname_arr[1] ?? '';
                $country_id = null;

                if (!empty($input['country_code'])) {
                    $country_name = strtolower($input['country_code']); // Convert to lowercase
                    $country = Country::whereRaw('LOWER(name) LIKE ?', ["%{$country_name}%"])->first();

                    if ($country) {
                        $country_id = $country->id;
                    }
                }
                
                $myLeads = Lead::create([
                    'firstname' => $firstname ?? '',
                    'lastname' => $lastname ?? '',
                    'email' => $input['email'] ?? '',
                    'mobile' => $input['mobile'] ?? '',
                    'requirement' => $input['requirement'] ?? '',
                    'country_id' => $country_id ?? null,
                    'lead_status_id' => 1,
                    'lead_source_id' => 5,
                    'workspace_id' => $workspace_id
                ]);

                $messages = [
                    'token' => config('slack.TOKEN'),
                    'channel' => '#' . config('slack.CHANNEL'),
                    'text' => "#) New Lead From " . $input['page_url'] . "\nName: " . $input['firstname'] . "\nEmail: " . $input['email'] . "\nCountry Code: " . $input['country_code'] . "\nContact Number: " . $input['mobile'] . "\nRequirement: " . $input['requirement'] . "\n Request IP: " . $input['IP'], 'as_user' => true
                ];
                SlackHelper::messages($messages);

                if($input['email'] != ''){
                    $to = $input['email'];
                    Mail::mailer(config('mail.default'))
                        ->to($to)
                        ->send(new WelcomeMail($firstname));

                    CronActivityLogHelper::log(
                        'lead.created',
                        'Welcome mail send successfully for '. $input['firstname'] .' ('.$to.').' ,
                        [],
                        $request,
                        null,
                        $myLeads,
                        $workspace_id
                    );
                }

                CronActivityLogHelper::log(
                    'lead.created',
                    'Lead received from website',
                    [],
                    $request,
                    null,
                    $myLeads,
                    $workspace_id
                );

                // $leads = $myLeads->save();
            }
            return response()->json(['status' => true, 'message' => "created"]);
        } catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => $th->getMessage(),
                "error" => $th,
            ], 500);
        }
    }
}
