<?php

namespace App\Helpers;

class MailHelper
{
    public static function getMailerInstance($host = '', $port = '', $encryption = '', $username = '', $secret = '')
    {
        $transport = (new \Swift_SmtpTransport($host, $port))
            ->setEncryption($encryption)
            ->setUsername($username)
            ->setPassword($secret);

        $mailer = app(\Illuminate\Mail\Mailer::class);
        $mailer->setSwiftMailer(new \Swift_Mailer($transport));

        return $mailer;
    }
}
