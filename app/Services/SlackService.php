<?php

namespace App\Services;

use App\Contracts\CommunicationAPI;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService implements CommunicationAPI
{
    public function sendMessage(string $msg): bool
    {
        try {

            $ch = curl_init(config('services.slack.url'));
            if(Auth::user()->active_workspace->slug === 'shalin-designs'){
                $ch = curl_init(config('services.slack_shalin_designs.url'));

            }

            curl_setopt($ch, CURLOPT_POST, 1); // Post request

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode([
                    'text' => $msg,
                ])
            ); // request body

            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array('Content-Type: application/json')
            ); // request header for json content

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                Log::info("SLACK CURL ERROR: ");
                Log::error($error_msg);
                throw new \Exception($error_msg);
            }
            curl_close($ch);

            return true;
        } catch (\Throwable $th) {
            Log::info($th);
            return false;
        }
    }

    public function sendWebhookMessage(string $msg,$workspace): bool
    {
        try {

            $ch = curl_init(config('services.slack.url'));
            if($workspace === 'shalin-designs'){
                $ch = curl_init(config('services.slack_shalin_designs.url'));

            }

            curl_setopt($ch, CURLOPT_POST, 1); // Post request

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode([
                    'text' => $msg,
                ])
            ); // request body

            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array('Content-Type: application/json')
            ); // request header for json content

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                Log::info("SLACK CURL ERROR: ");
                Log::error($error_msg);
                throw new \Exception($error_msg);
            }
            curl_close($ch);

            return true;
        } catch (\Throwable $th) {
            Log::info($th);
            return false;
        }
    }
}
