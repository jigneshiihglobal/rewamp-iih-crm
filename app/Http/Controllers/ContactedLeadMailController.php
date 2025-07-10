<?php

namespace App\Http\Controllers;

use App\Helpers\EncryptionHelper;
use App\Models\ContactedLeadMail;
use App\Models\MarketingMailStatus;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactedLeadMailController extends Controller
{
    public function index(Request $request){

        if ($request->ajax()) {
            $contacted_mails = ContactedLeadMail::with('lead_status')
                ->when($request->mail_status, function ($q, $mail_status) {
                    if($mail_status == 'Sent'){
                        $q->whereNotNull('webhook_id')->whereNotNull('message_id')->whereNotNull('lead_status_event');
                    }elseif($mail_status == 'Delivered'){
                        $q->whereNotNull('webhook_id')->whereNotNull('message_id')->where('lead_status_event','Delivered');
                    }elseif($mail_status == 'Open'){
                        $q->whereNotNull('webhook_id')->whereNotNull('message_id')->where('lead_status_event','Open');
                    }elseif($mail_status == 'Soft Bounce'){
                        $q->whereNotNull('webhook_id')->whereNotNull('message_id')->where('lead_status_event','Soft Bounce');
                    }
                })
            ->orderBy('created_at', 'desc'); // Order by created_at in descending order

            return DataTables::of($contacted_mails)->toJson();
        }

        $mail_count['total_counts'] = ContactedLeadMail::whereNotNull('webhook_id')->whereNotNull('message_id')->whereNotNull('lead_status_event')->get()->count();
        $mail_count['delivereds'] = ContactedLeadMail::where('lead_status_event','Delivered')->get()->count();
        $mail_count['opens'] = ContactedLeadMail::where('lead_status_event','Open')->get()->count();
        $mail_count['soft_bounce'] = ContactedLeadMail::where('lead_status_event','Soft Bounce')->get()->count();

        // mail delivereds percentage
        $delivereds = $mail_count['delivereds'];
        if ($mail_count['delivereds'] == '0') {
            $delivereds = ($mail_count['delivereds'] + $mail_count['opens']);
        }
        $percentageDelivered = $mail_count['total_counts'] != 0 ? ($delivereds * 100) / $mail_count['total_counts'] : '0' ;

        // mail open percentage
        $percentageOpens = $mail_count['total_counts'] != 0 ? ($mail_count['opens'] * 100) / $mail_count['total_counts'] : '0' ;

        // mail Bounce percentage
        $percentageBounce = $mail_count['total_counts'] != 0 ? ($mail_count['soft_bounce'] * 100) / $mail_count['total_counts'] : '0';

        return view('contacted_lead.index',compact('mail_count','percentageDelivered','percentageOpens','percentageBounce'));
    }

    public function show(Request $request){
        try {
            $id = EncryptionHelper::decrypt($request->encId);

            $mail_content = ContactedLeadMail::select('mail_content')->where('id',$id)->first();
            $lead_mail_content = $mail_content->mail_content ?? 'No Mail Content Found';
            return response()->json([
                'success' => true,
                'mail_content' => $lead_mail_content,
            ], 200);
        }catch (\Exception $th){
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while sending mail!',
                'error' => $th
            ], 500);
        }
    }
}
