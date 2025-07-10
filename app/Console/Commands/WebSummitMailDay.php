<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Mail\WebSummitMail;
use App\Models\ContactedLeadMail;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;

class WebSummitMailDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'web_summit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->line("[" . date('Y-m-d H:i:s') . "] Running Command \"" . $this->signature . "\"");
        try {
            $lead_status_ids = LeadStatus::whereIn('title', ['Contacted', 'Estimated', 'Lost', 'Hold', 'Future Follow Up'])->pluck('id');
            $workspaces = Workspace::where('name','IIH Global')->first();
            $leads = Lead::whereIn('lead_status_id', $lead_status_ids)->where('workspace_id',$workspaces->id)->whereNotNull('email')->where('web_summit_mail_status',0)->limit(100)->get();

            foreach ($leads as $lead){
                $lead_emails = $lead->email;
                $email = explode(',',$lead_emails);
                //dd($lead_emails,$email);

                $mail =new WebSummitMail($lead);
                $view = view('emails.web_summit.web_summit_mail',compact('lead'))->render();
                $subject = "IIH Global at Web Summit 2024";

                if(isset($lead->email) && $lead->email != null){
                    $this->mailConfigMailer($email,$mail);
                    $this->sendCronActivityMail($lead);
                    $this->contactedLeadMailStore($lead,$view,$subject);
                }
            }

        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred web summit mail send : " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        }
    }

    public function mailConfigMailer($email,$mail)
    {
        Mail::mailer(config('mail.default'))
            ->to($email)
            ->send($mail);
    }

    public function contactedLeadMailStore($lead,$view,$subject)
    {
        $name = ($lead->firstname?? '').' '.($lead->lastname?? '');
        $mail_content = $view ?? '';
        $subject = $subject ?? '';

        $lead_mail =new ContactedLeadMail();
        $lead_mail->lead_status_id	= $lead->lead_status_id ?? '';
        $lead_mail->lead_name	    = $name;
        $lead_mail->email	        = $lead->email;
        $lead_mail->mail_subject	= $subject;
        $lead_mail->mail_content	= $mail_content;
        $lead_mail->save();

        DB::table('leads')->where('id', $lead->id)->update(['web_summit_mail_status' => 1], ['updated_at' => false]);
    }

    public function sendCronActivityMail(Lead $lead)
    {
        $workspace_id = 1;
        CronActivityLogHelper::log(
            'leads',
            'Web summit mail send to ' . $lead->email,
            [],
            request(),
            User::role('Superadmin')->first(),
            $lead,
            $workspace_id
        );
    }

    public function sendCronErrorMail(\Throwable $th = null, string $title = '')
    {
        try {
            Mail::to(
                config(
                    'custom.cron_email_recipients.error_mail_address',
                    []
                )
            )
                ->send(new CronErrorMail($title, $th, "Web summit - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
