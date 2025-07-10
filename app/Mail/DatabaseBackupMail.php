<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;
    public string $file;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('custom.mail.from.name.db_backup'))
            ->view('emails.cron.db_backup')
            ->attach(
                $this->file,
                [
                    'as' => 'iih_crm.sql',
                    'mime' => 'application/sql'
                ]
            );
    }
}
