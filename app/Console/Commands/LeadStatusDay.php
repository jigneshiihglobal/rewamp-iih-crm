<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Mail\TwoDayLeadMail;
use App\Mail\SixDayLeadMail;
use App\Mail\TenDayLeadMail;
use App\Mail\FifteenDayLeadMail;
use App\Mail\TwentyDayLeadMail;
use App\Mail\TwentySevenDayLeadMail;
use App\Models\ContactedLeadMail;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;
class LeadStatusDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacted_lead_status';

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

        $orig_max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 180); // 3 min
        try {
            $lead_status = LeadStatus::whereIn('title',['Contacted','Estimated'])->get()->pluck('id');
            $workspaces = Workspace::where('name','IIH Global')->first();
            $leads = Lead::whereIn('lead_status_id',[$lead_status[0],$lead_status[1]])->where('workspace_id',$workspaces->id)->where('marketing_mail_reminder_status','1')->whereNotNull('status_date')->get();

            foreach ($leads as $lead){
                $lead_day = $lead->status_date;
                $day_count = today()->diffInDays($lead_day);
                $lead_emails = $lead->email;
                $email = explode(',',$lead_emails);
                $day = '';

                if($day_count == 2){ /* 2 day Send Mail */
                    $mail =new TwoDayLeadMail($lead);
                    $view = view('emails.day_cron.two_day',compact('lead'))->render();
                    $subject = "How we helped Pharmsmart get in 7000+ stores in the UK ";
                    $day = 'After 2 Days';
                }elseif ($day_count == 6){ /* 6 day Send Mail */
                    $mail =new SixDayLeadMail($lead);
                    $view = view('emails.day_cron.six_day',compact('lead'))->render();
                    $subject = "Do you have my phone number?";
                    $day = 'After 6 Days';
                }elseif ($day_count == 10){ /* 10 day Send Mail */
                    $mail =new TenDayLeadMail($lead);
                    $view = view('emails.day_cron.ten_day',compact('lead'))->render();
                    $subject = "How we helped Five Star Catering Staff get the right clothes";
                    $day = 'After 10 Days';
                }elseif ($day_count == 15){ /* 15 day Send Mail */
                    $mail =new FifteenDayLeadMail($lead);
                    $view = view('emails.day_cron.fifteen_day',compact('lead'))->render();
                    $subject = "You won't believe it - I'm famous";
                    $day = 'After 15 Days';
                }elseif ($day_count == 20){ /* 20 day Send Mail */
                    $mail =new TwentyDayLeadMail($lead);
                    $view = view('emails.day_cron.twenty_day',compact('lead'))->render();
                    $subject = "Making your business better by 80% with a custom platform";
                    $day = 'After 20 Days';
                }elseif ($day_count == 27){ /* 27 day Send Mail */
                    $mail =new TwentySevenDayLeadMail($lead);
                    $view = view('emails.day_cron.twenty_seven_day',compact('lead'))->render();
                    $subject = "What's your mission?";
                    $day = 'After 27 Days';
                }

                if($day != '' && isset($lead->email) && $lead->email != null){
                    $this->mailConfigMailer($email,$mail);
                    $this->sendCronActivityMail($lead,$day);
                    $this->line("[" . date('Y-m-d H:i:s') . "] Running cron for " . $day . " Lead Contacted OR Estimated " . $lead_emails .".");
                    $this->contactedLeadMailStore($lead,$day,$view,$subject);
                }
            }

        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred lead contacted mail send : " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        }finally {
            ini_set('max_execution_time', $orig_max_exec_time); // revert back to original settings
        }
    }

    public function mailConfigMailer($email,$mail)
    {
        Mail::mailer(config('mail.default'))
            ->to($email)
            ->send($mail);
    }

    public function contactedLeadMailStore($lead,$day,$view,$subject)
    {
        $name = ($lead->firstname?? '').' '.($lead->lastname?? '');
        $mail_content = $view ?? '';
        $subject = $subject ?? '';

        $lead_mail =new ContactedLeadMail;
        $lead_mail->lead_status_id	= $lead->lead_status_id ?? '';
        $lead_mail->lead_name	    = $name;
        $lead_mail->email	        = $lead->email;
        $lead_mail->day_after	    = $day;
        $lead_mail->mail_subject	= $subject;
        $lead_mail->mail_content	= $mail_content;
        $lead_mail->save();

        DB::table('leads')->where('id', $lead->id)->update(['con_est_sent_mail_at' => Carbon::now()], ['updated_at' => false]);
    }

    public function sendCronActivityMail(Lead $lead,$day)
    {
        ActivityLogHelper::log(
            'leads',
            $day.' Mail send to ' . $lead->email,
            [],
            request(),
            User::role('Superadmin')->first(),
            $lead
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
                ->send(new CronErrorMail($title, $th, "Lead Contacted Mail - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
