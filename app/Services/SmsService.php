<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS notification
     * 
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function sendSms(string $phoneNumber, string $message): bool
    {
        try {
            // Clean phone number (remove any non-numeric characters except +)
            $phoneNumber = $this->cleanPhoneNumber($phoneNumber);
            
            // Get SMS provider from config
            $provider = config('services.sms.provider', 'twilio');
            
            switch ($provider) {
                case 'twilio':
                    return $this->sendViaTwilio($phoneNumber, $message);
                case 'nexmo':
                    return $this->sendViaNexmo($phoneNumber, $message);
                case 'custom':
                    return $this->sendViaCustom($phoneNumber, $message);
                default:
                    // Fallback: log the message (for development/testing)
                    Log::info('SMS would be sent', [
                        'phone' => $phoneNumber,
                        'message' => $message
                    ]);
                    return true;
            }
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio(string $phoneNumber, string $message): bool
    {
        $accountSid = config('services.sms.twilio.account_sid');
        $authToken = config('services.sms.twilio.auth_token');
        $from = config('services.sms.twilio.from');

        if (!$accountSid || !$authToken || !$from) {
            Log::warning('Twilio credentials not configured');
            return false;
        }

        try {
            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                    'From' => $from,
                    'To' => $phoneNumber,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info('SMS sent via Twilio', ['phone' => $phoneNumber]);
                return true;
            }

            Log::error('Twilio API error', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Twilio SMS error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send SMS via Nexmo/Vonage
     */
    private function sendViaNexmo(string $phoneNumber, string $message): bool
    {
        $apiKey = config('services.sms.nexmo.api_key');
        $apiSecret = config('services.sms.nexmo.api_secret');
        $from = config('services.sms.nexmo.from');

        if (!$apiKey || !$apiSecret || !$from) {
            Log::warning('Nexmo credentials not configured');
            return false;
        }

        try {
            $response = Http::post('https://rest.nexmo.com/sms/json', [
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'from' => $from,
                'to' => $phoneNumber,
                'text' => $message,
            ]);

            if ($response->successful() && $response->json('messages.0.status') === '0') {
                Log::info('SMS sent via Nexmo', ['phone' => $phoneNumber]);
                return true;
            }

            Log::error('Nexmo API error', ['response' => $response->json()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Nexmo SMS error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send SMS via custom API
     */
    private function sendViaCustom(string $phoneNumber, string $message): bool
    {
        $url = config('services.sms.custom.url');
        $apiKey = config('services.sms.custom.api_key');

        if (!$url || !$apiKey) {
            Log::warning('Custom SMS credentials not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'phone' => $phoneNumber,
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info('SMS sent via custom provider', ['phone' => $phoneNumber]);
                return true;
            }

            Log::error('Custom SMS API error', ['response' => $response->json()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Custom SMS error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clean phone number format
     */
    private function cleanPhoneNumber(string $phoneNumber): string
    {
        // Remove all characters except digits and +
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // If it doesn't start with +, assume it's a local number and add country code
        if (!str_starts_with($phoneNumber, '+')) {
            $countryCode = config('services.sms.default_country_code', '+63'); // Default to Philippines
            // Remove leading 0 if present
            if (str_starts_with($phoneNumber, '0')) {
                $phoneNumber = substr($phoneNumber, 1);
            }
            $phoneNumber = $countryCode . $phoneNumber;
        }
        
        return $phoneNumber;
    }

    /**
     * Send pre-order available notification
     */
    public function sendPreOrderAvailableNotification($order): bool
    {
        $buyer = $order->buyer;
        $product = $order->riceProduct;

        if (!$buyer->phone) {
            Log::warning('Buyer has no phone number', ['buyer_id' => $buyer->id]);
            return false;
        }

        $message = "Hello {$buyer->name}, your pre-ordered rice product '{$product->name}' is now available! You can pick it up or we will deliver it to you soon. Order #{$order->id}";

        return $this->sendSms($buyer->phone, $message);
    }

    /**
     * Send day-before pickup notification
     */
    public function sendDayBeforePickupNotification($order): bool
    {
        $buyer = $order->buyer;
        $product = $order->riceProduct;
        $availableDate = $order->available_date ?? $order->expected_delivery_date;

        if (!$buyer->phone || !$availableDate) {
            Log::warning('Cannot send day-before notification', [
                'buyer_id' => $buyer->id,
                'has_date' => !!$availableDate
            ]);
            return false;
        }

        $formattedDate = $availableDate->format('F j, Y');
        $message = "Hello {$buyer->name}, reminder: Your pre-ordered rice product '{$product->name}' will be available tomorrow ({$formattedDate}). Order #{$order->id}";

        return $this->sendSms($buyer->phone, $message);
    }
}






