<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginInfoToAdminMail extends Mailable
{
    use Queueable, SerializesModels;
    public $browser, $device, $deviceType, $ip, $isRobot, $loginTime, $platform, $robot, $user, $lat, $long, $hostname, $location;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($browser, $device, $deviceType, $ip, $isRobot, $loginTime, $platform, $robot, $user, $lat, $long, $hostname, $location)
    {
        $this->browser    = $browser;
        $this->device     = $device;
        $this->deviceType = $deviceType;
        $this->ip         = $ip;
        $this->isRobot    = $isRobot;
        $this->loginTime  = $loginTime;
        $this->platform   = $platform;
        $this->robot      = $robot;
        $this->user       = $user;
        $this->long       = $long;
        $this->lat        = $lat;
        $this->hostname   = $hostname;
        $this->location   = $location;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(config('mail.accounts_mail_from.address'), config('custom.mail.from.name.login_info'))
            ->subject("IIH CRM - " . ($this->user->full_name ?? "") . " logged in")
            ->view('emails.auth.login_info_admin_mail');
    }
}
