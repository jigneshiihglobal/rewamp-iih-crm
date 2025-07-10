<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Models\Client;
use App\Models\ContactedLeadMail;
use App\Models\LeadStatus;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientReferAndEarnMail;
use DB;

class ReferAndEarn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refer_and_earn';

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
        ini_set('max_execution_time', 240); // 4 min

        try {
            $workspace = Workspace::where('slug','iih-global')->first();
            $clients = Client::where('workspace_id',$workspace->id)->whereNull('refer_earn_mail_at')->where('is_refer_earn_mail','0')->get();

            foreach ($clients as $client) {
                $client_emails = $client->email;
                $email = explode(',',$client_emails);

                Mail::mailer(config('mail.default'))
                    ->to($email)
                    ->send(new ClientReferAndEarnMail($client));
                $view = view('emails.client_refer_earn.refer_and_earn',compact('client'))->render();

                $this->info("[" . date('Y-m-d H:i:s') . "]. Client refer and earn mail send successfully :" .$client_emails);
                $this->contactedLeadMailStore($client,$view);
            }

        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred client refer and earn mail send : " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        }finally {
            ini_set('max_execution_time', $orig_max_exec_time); // revert back to original settings
        }
    }

    public function contactedLeadMailStore($client)
    {
        $name = $client->name ?? '';
        $email = $client->email ?? '';
        $subject = 'Introducing Our Refer and Earn Program: Earn $100 Cash or Amazon Voucher!';
        $mail_content = $view ?? '';

        $mail_sent =new ContactedLeadMail;
        $mail_sent->lead_status_id	= null;
        $mail_sent->lead_name	    = $name;
        $mail_sent->email	        = $email;
        $mail_sent->day_after	    = null;
        $mail_sent->mail_subject	= $subject;
        $mail_sent->mail_content	= $mail_content;
        $mail_sent->save();

        DB::table('clients')->where('id', $client->id)->update(['refer_earn_mail_at' => Carbon::now(),'is_refer_earn_mail' => '1'], ['updated_at' => false]);
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
                ->send(new CronErrorMail($title, $th, "Client refer and earn - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
