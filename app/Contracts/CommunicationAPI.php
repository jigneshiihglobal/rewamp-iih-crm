<?php

namespace App\Contracts;

interface CommunicationAPI {
    public function sendMessage(string $msg) : bool;

    public function sendWebhookMessage(string $msg,$workspace) : bool;
    
}
