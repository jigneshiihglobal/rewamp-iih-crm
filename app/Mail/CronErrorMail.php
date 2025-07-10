<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CronErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    public Throwable $error;
    public string $title;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $title, Throwable $error, $subject)
    {
        $this->error = $error;
        $this->title = $title;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('custom.mail.from.name.sub_cron_error'))
            ->subject($this->subject)
            ->view('emails.cron.cron-error');
    }
}
