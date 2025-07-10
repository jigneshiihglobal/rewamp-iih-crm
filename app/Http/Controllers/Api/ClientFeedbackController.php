<?php

namespace App\Http\Controllers\Api;

use App\Helpers\EncryptionHelper;
use App\Http\Controllers\Controller;
use App\Mail\FeedbackMail;
use App\Models\Client;
use App\Models\FeedbackToken;
use Illuminate\Http\Request;
use App\Models\ClientFeedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ClientFeedbackController extends Controller
{
    public function clientReviewFeedback(Request $request){
        try{

            // Validate the request
            $validated = $request->validate([
                'client_id'            => 'required',
                'feedback_token'       => 'required',
                'communication'        => 'required|integer|min:1|max:5',
                'quality_of_work'      => 'required|integer|min:1|max:5',
                'collaboration'        => 'required|integer|min:1|max:5',
                'value_for_money'      => 'required|integer|min:1|max:5',
                'overall_satisfaction' => 'required|integer|min:1|max:5',
                'recommendation'       => 'required|boolean',
            ]);
            
           $client_id = EncryptionHelper::decrypt($validated['client_id']);

           $client_feedback                         = new ClientFeedback();
           $client_feedback->client_id              = $client_id;
           $client_feedback->communication          = $validated['communication'];
           $client_feedback->quality_of_work        = $validated['quality_of_work'];
           $client_feedback->collaboration          = $validated['collaboration'];
           $client_feedback->value_for_money        = $validated['value_for_money'];
           $client_feedback->overall_satisfaction   = $validated['overall_satisfaction'];
           $client_feedback->recommendation         = $validated['recommendation'];
           $client_feedback->message_box            = $request->message_box ?? null;
           $client_feedback->save();

           $feedback_token = FeedbackToken::where('client_id',$client_id)->where('feedback_form_token',$validated['feedback_token'])->where('is_used',0)->first();
           $feedback_token->is_used = 1;
           $feedback_token->save();

           // Feedback mail send
           $client = Client::where('id',$client_id)->first();

           $to = config('mail.feedback_received_mail_address');

           $data = [
                'name' => $client->name,
                'email' => $client->email,
                'feedback_date' => Carbon::now('Europe/London')->format('d-m-Y h:i A'),
            ];

           Mail::mailer(config('mail.default'))
           ->to($to)
           ->send(new FeedbackMail($data));

            return response()->json(['success' => true, 'message' => "feedback saved!"]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "success" => false,
                "message" => "Validation error.",
                "errors" => $e->errors()
            ], 422);
        }  catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong. Please try again later!",
            ], 500);
        }
    }

    public function feedbackTokenCheck(Request $request){
        try{

            // Validate the request
            $validated = $request->validate([
                'client_id'            => 'required',
                'feedback_token'       => 'required',
            ]);

            $client_id = EncryptionHelper::decrypt($validated['client_id']);
            $feedback_token = $validated['feedback_token'];

            $token_is_exist_or_not = FeedbackToken::where('client_id',$client_id)->where('feedback_form_token',$feedback_token)->count();
            if($token_is_exist_or_not == 0){
                return response()->json(['success' => false, 'message' => "client or feedback-token not exist",'is_used' => 2]);
            }

            $feedback_form_token = FeedbackToken::where('client_id',$client_id)->where('feedback_form_token',$feedback_token)->where('is_used',1)->count();

            if(isset($feedback_form_token) && $feedback_form_token > 0){
                return response()->json(['success' => false, 'message' => "feedback token used!",'is_used' => 1]);
            }else{
                return response()->json(['success' => true, 'message' => "feedback token not-used!",'is_used' => 0]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "success" => false,
                "message" => "Validation error.",
                "errors" => $e->errors()
            ], 422);
        }  catch (\Throwable $th) {
            return response()->json([
                "success" => false,
                "message" => "Something went wrong. Please try again later!",
            ], 500);
        }
    }
}
