<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Mail\FollowUpFirstMail;
use App\Mail\FollowUpSecondMail;
use App\Mail\FollowUpThirdMail;
use App\Mail\FollowUpForthMail;
use App\Mail\FollowUpFifthMail;
use App\Mail\FollowUpSixMail;
use App\Mail\FollowUpSevenMail;
use App\Mail\FollowUpEightMail;
use App\Mail\FollowUpNineMail;
use App\Mail\FollowUpTenMail;
use App\Models\ContactedLeadMail;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;

class FollowUpAndLost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'follow_up_and_lost';

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
            $lead_status = LeadStatus::whereIn('title',['Lost','Future Follow Up'])->get()->pluck('id');
            $workspaces = Workspace::where('name','IIH Global')->first();
            $leads = Lead::whereIn('lead_status_id',[$lead_status[0],$lead_status[1]])->where('workspace_id',$workspaces->id)->where('marketing_mail_reminder_status','1')->whereNotNull('follow_up_at')->get();

            foreach ($leads as $lead){
                $lead_day = $lead->follow_up_at;
                $day_count = today()->diffInDays($lead_day);
                $lead_emails = $lead->email;
                $email = explode(',',$lead_emails);
                $day = '';

                if($day_count == 30){ /* 30 day Send Mail */
                    $mail =new FollowUpFirstMail($lead);
                    $view = view('emails.follow_up_day.first_mail',compact('lead'))->render();
                    $subject = "THIS can be scary";
                    $day = 'After 30 Days';
                }elseif($day_count == 37){ /* 37 day Send Mail */
                    $mail =new FollowUpSecondMail($lead);
                    $view = view('emails.follow_up_day.second_mail',compact('lead'))->render();
                    $subject = "don’t read this if you want to change";
                    $day = 'After 37 Days';
                }elseif($day_count == 44){ /* 44 day Send Mail 30-3 */
                    $mail =new FollowUpThirdMail($lead);
                    $view = view('emails.follow_up_day.third_mail',compact('lead'))->render();
                    $subject = "How to make the world a safer place";
                    $day = 'After 44 Days';
                }elseif($day_count == 51){ /* 51 day Send Mail 23-3*/
                    $mail =new FollowUpForthMail($lead);
                    $view = view('emails.follow_up_day.forth_mail',compact('lead'))->render();
                    $subject = "Do this to get the best of both world";
                    $day = 'After 51 Days';
                }elseif($day_count == 58){ /* 58 day Send Mail 16-3*/
                    $mail =new FollowUpFifthMail($lead);
                    $view = view('emails.follow_up_day.fifth_mail',compact('lead'))->render();
                    $subject = "Is your shopfront broken?";
                    $day = 'After 58 Days';
                }elseif($day_count == 65){ /* 65 day Send Mail 9-3*/
                    $mail =new FollowUpSixMail($lead);
                    $view = view('emails.follow_up_day.six_mail',compact('lead'))->render();
                    $subject = "The key to a decade of great service";
                    $day = 'After 65 Days';
                }elseif($day_count == 72){ /* 72 day Send Mail */
                    $mail =new FollowUpSevenMail($lead);
                    $view = view('emails.follow_up_day.seven_mail',compact('lead'))->render();
                    $subject = "Are you building a scalable business?"; // subject
                    $day = 'After 72 Days';
                }elseif($day_count == 79){ /* 79 day Send Mail */
                    $mail =new FollowUpEightMail($lead);
                    $view = view('emails.follow_up_day.eight_mail',compact('lead'))->render();
                    $subject = "The reason freelancers let you down"; // subject
                    $day = 'After 79 Days';
                }elseif($day_count == 86){ /* 86 day Send Mail*/
                    $mail =new FollowUpNineMail($lead);
                    $view = view('emails.follow_up_day.nine_mail',compact('lead'))->render();
                    $subject = "Stop doing this money burning mistake"; // subject
                    $day = 'After 86 Days';
                }elseif($day_count == 93){ /* 93 day Send Mail*/
                    $mail =new FollowUpTenMail($lead);
                    $view = view('emails.follow_up_day.ten_mail',compact('lead'))->render();
                    $subject = "Ghosted by your developer?"; // subject
                    $day = 'After 93 Days';
                }

                if($day != '' && isset($lead->email) && $lead->email != null){
                    $this->mailConfigMailer($email,$mail);
                    $this->sendCronActivityMail($lead,$day);
                    $this->line("[" . date('Y-m-d H:i:s') . "] Running cron for " . $day . " Lead Lost OR Follow up " . $lead_emails .".");
                    $this->followupLeadMailStore($lead,$day,$view,$subject);
                }
            }

        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred lead follow up mail send : " . $th->getMessage());
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

    public function followupLeadMailStore($lead,$day,$view,$subject)
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

        DB::table('leads')->where('id', $lead->id)->update(['follow_up_sent_mail_at' => Carbon::now()],['updated_at' => false]);
    }

    public function sendCronActivityMail(Lead $lead,$day)
    {
        $workspace_id = 1;
        CronActivityLogHelper::log(
            'leads',
            $day.' Mail send to ' . $lead->email,
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
                ->send(new CronErrorMail($title, $th, "Lead FollowUp and Lost Mail - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
