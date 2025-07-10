<?php

namespace App\Console\Commands;

use App\Enums\FollowUpStatus;
use App\Enums\FollowUpType;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Helpers\MailHelper;
use App\Mail\CronErrorMail;
use App\Mail\FollowUpCallReminder;
use App\Mail\FollowUpLeadMail;
use App\Models\FollowUp;
use App\Models\LeadNote;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class RemindToFollowUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'follow_ups:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a reminder mail for pending follow ups due today';

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
        try {

            $fifteenMinLater = new DateTime('+15 minutes', new DateTimeZone('UTC')); // Now plus 15 min (15 min later time)

            $follow_ups = FollowUp::query()
                ->select(
                    'follow_ups.id',
                    'follow_ups.lead_id',
                    'follow_ups.sales_person_id',
                    'follow_ups.follow_up_at',
                    'follow_ups.send_reminder_at',
                    'follow_ups.content',
                    'follow_ups.to',
                    'follow_ups.bcc',
                    'follow_ups.subject',
                    'follow_ups.sales_person_phone',
                    'follow_ups.email_signature_id',
                    'follow_ups.smtp_credential_id',
                    'follow_ups.type',
                )
                ->with([
                    'lead:id,firstname,lastname,email',
                    'sales_person:id,first_name,last_name,email,timezone',
                    'email_signature:id,user_id,name,email,position,mobile_number,image_link,workspace_id',
                    'email_signature.workspace:id,name,slug',
                    'smtp_credential:id,user_id,host,port,encryption,username,password,from_name,from_address',
                    'sales_person.smtp_credentials:id,user_id,host,port,encryption,username,password,from_name,from_address',
                ])
                ->where('follow_ups.status', FollowUpStatus::PENDING)
                ->where('follow_ups.send_reminder_at', "<", $fifteenMinLater)
                ->cursor();

            if ($follow_ups->count()) {
                foreach ($follow_ups as $follow_up) {

                    $this->line("[" . date('Y-m-d H:i:s') . "] Sending follow up reminder for lead: " . $follow_up->lead->firstname . " " .  $follow_up->lead->lastname);

                    try {

                        switch ($follow_up->type) {
                            case FollowUpType::CALL:

                                Mail::to($follow_up->sales_person)
                                    ->send(new FollowUpCallReminder($follow_up));
                                $workspace_id = 1;
                                CronActivityLogHelper::log(
                                    'follow_ups.reminder.mail-sent',
                                    'Follow up reminder Mail sent to sales person',
                                    [],
                                    request(),
                                    $follow_up->sales_person,
                                    $follow_up,
                                    $workspace_id
                                );

                                // send SMS as well to sales person

                                break;

                            default:

                                $to = $follow_up->to ?? [];
                                $bcc = $follow_up->bcc ?? [];
                                $bcc = array_unique($bcc);

                                if ($follow_up->smtp_credential) {
                                    $mailer = MailHelper::getMailerInstance(
                                        $follow_up->smtp_credential->host,
                                        $follow_up->smtp_credential->port,
                                        $follow_up->smtp_credential->encryption,
                                        $follow_up->smtp_credential->username,
                                        $follow_up->smtp_credential->secret
                                    );
                                    $mailer->to($to)
                                        ->bcc($bcc)
                                        ->send(new FollowUpLeadMail($follow_up));
                                } else if ($follow_up->sales_person->smtp_credentials->first()) {
                                    $mailer = MailHelper::getMailerInstance(
                                        $follow_up->sales_person->smtp_credentials->first()->host,
                                        $follow_up->sales_person->smtp_credentials->first()->port,
                                        $follow_up->sales_person->smtp_credentials->first()->encryption,
                                        $follow_up->sales_person->smtp_credentials->first()->username,
                                        $follow_up->sales_person->smtp_credentials->first()->secret
                                    );
                                    $mailer->to($to)
                                        ->bcc($bcc)
                                        ->send(new FollowUpLeadMail($follow_up));
                                } else {
                                    Mail::to($to)
                                        ->bcc($bcc)
                                        ->send(new FollowUpLeadMail($follow_up));
                                }

                                LeadNote::create([
                                    'lead_id' => $follow_up->lead_id,
                                    'user_id' => $follow_up->sales_person_id,
                                    'note' => "Follow up sent successfully",
                                ]);

                                ActivityLogHelper::log(
                                    'follow_ups.mail.mail-sent',
                                    'Follow up Mail sent to lead',
                                    [],
                                    request(),
                                    $follow_up->sales_person,
                                    $follow_up
                                );

                                break;
                        }

                        $follow_up->update(['status' => FollowUpStatus::COMPLETED]);

                        $this->info("[" . date('Y-m-d H:i:s') . "] Successfully sent follow up reminder for lead: " . $follow_up->lead->firstname . " " .  $follow_up->lead->lastname);
                    } catch (\Throwable $th) {
                        $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending reminder mail for lead: " . $follow_up->lead->firstname . " " .  $follow_up->lead->lastname);
                        $this->error($th);
                        $this->sendCronErrorMail($th, "Error occurred while running follow up cron for lead: ". $follow_up->lead->firstname . " ". $follow_up->lead->lastname);
                    }
                }
            }
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred: " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, "Error occurred while running follow up cron");
        }

        return 0;
    }

    private function sendCronErrorMail(Throwable $th = null, string $title = '')
    {
        try {
            Mail::to(
                config(
                    'custom.cron_email_recipients.error_mail_address',
                    []
                )
            )
                ->send(new CronErrorMail($title, $th, "Follow up CRON - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
