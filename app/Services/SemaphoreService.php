<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreService
{
    protected $apiKey;
    protected $senderName;
    protected $baseUrl = 'https://api.semaphore.co/api/v4';

    public function __construct()
    {
        $this->apiKey = config('services.semaphore.api_key');
        $this->senderName = config('services.semaphore.sender_name', 'SEMAPHORE');
    }

    /**
     * Send SMS via Semaphore API
     */
    public function sendSMS(string $to, string $message): bool
    {
        if (!$this->apiKey) {
            Log::error('Semaphore API key not configured');
            return false;
        }

        // Format Philippine number: remove leading 0, add +63 if needed
        $to = $this->formatPhoneNumber($to);

        try {
            Log::info("Attempting to send SMS via Semaphore to {$to}");

            $response = Http::asForm()->post("{$this->baseUrl}/messages", [
                'apikey' => $this->apiKey,
                'number' => $to,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully via Semaphore to {$to}", [
                    'response' => $response->json()
                ]);
                return true;
            }

            Log::error('Semaphore API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'to' => $to
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Semaphore SMS error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number for Semaphore (Philippine format)
     * Converts: 09171234567 -> 639171234567
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 63 (Philippine country code)
        if (str_starts_with($phone, '0')) {
            $phone = '63' . substr($phone, 1);
        }

        // If starts with +63, remove the +
        if (str_starts_with($phone, '63') && strlen($phone) === 12) {
            return $phone;
        }

        return $phone;
    }
}
