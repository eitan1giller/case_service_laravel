<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CaseNotification;

class NotificationSender
{
    protected SmsDriver $sms;

    public function __construct()
    {
        $this->sms = new SmsDriver();
    }

    /**
     * Deliver notifications based on outbox event payload.
     * Expects payload to include 'type' and 'data' keys.
     */
    public function deliver(array $payload): void
    {
        // Example payload for case created:
        // ['channel' => 'email', 'to' => 'user@example.com', 'subject' => '...', 'body' => '...']

        $channel = $payload['channel'] ?? null;
        if (!$channel) {
            Log::warning('NotificationSender: no channel in payload', $payload);
            return;
        }

        if ($channel === 'email') {
            $to = $payload['to'] ?? null;
            if ($to) {
                Mail::to($to)->send(new CaseNotification($payload));
                Log::info('NotificationSender: email sent', ['to' => $to]);
            }
            return;
        }

        if ($channel === 'sms') {
            $to = $payload['to'] ?? null;
            $message = $payload['body'] ?? '';
            if ($to) {
                $this->sms->send($to, $message);
            }
            return;
        }

        Log::warning('NotificationSender: unsupported channel', ['channel' => $channel]);
    }
}
