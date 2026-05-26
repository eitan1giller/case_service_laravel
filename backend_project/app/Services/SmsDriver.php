<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsDriver
{
    /**
     * Demo SMS send — logs the message. Replace with real provider (Twilio, etc.)
     */
    public function send(string $to, string $message): bool
    {
        Log::info('SMS send (demo)', ['to' => $to, 'message' => $message]);
        return true;
    }
}
