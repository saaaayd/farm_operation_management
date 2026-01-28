<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->from = config('services.twilio.from');
        
        // Fix common format issue: +6309... -> +639...
        if (str_starts_with($this->from, '+630')) {
            $this->from = '+63' . substr($this->from, 4);
        }

        if ($sid && $token) {
            $this->client = new Client($sid, $token);
        } else {
            Log::error('Twilio credentials missing in config', [
                'sid' => $sid ? 'Set' : 'Missing',
                'token' => $token ? 'Set' : 'Missing',
                'from' => $this->from
            ]);
        }
    }

    public function sendSMS($to, $message)
    {
        if (!$this->client) {
            Log::warning('Twilio client not initialized. SMS not sent.', ['to' => $to]);
            return false;
        }

        try {
            Log::info("Attempting to send SMS to {$to} from {$this->from}");
            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message
            ]);
            Log::info("SMS sent successfully to {$to}");
            return true;
        } catch (\Exception $e) {
            Log::error('Twilio SMS Error: ' . $e->getMessage());
            return false;
        }
    }
}
