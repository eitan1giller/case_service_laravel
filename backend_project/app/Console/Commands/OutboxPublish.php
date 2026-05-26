<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OutboxEvent;
use App\Services\NotificationSender;
use Illuminate\Support\Facades\Log;

class OutboxPublish extends Command
{
    protected $signature = 'outbox:publish {--once : Run only one batch then exit}';
    protected $description = 'Publish pending outbox events to external systems (demo implementation).';

    public function handle()
    {
        $this->info('Outbox publisher started');

        do {
            $events = OutboxEvent::where('published', false)
                ->orderBy('id')
                ->limit(20)
                ->get();

            if ($events->isEmpty()) {
                $this->info('No pending events');
                break;
            }

            foreach ($events as $event) {
                try {
                    $this->info('Publishing event id=' . $event->id . ' type=' . $event->event_type);
                    Log::info('Outbox publish', ['id' => $event->id, 'event' => $event->toArray()]);

                    // Demo delivery: if the payload contains notifications, deliver them.
                    $payload = is_array($event->payload) ? $event->payload : (array) $event->payload;
                    $sender = new NotificationSender();

                    if (!empty($payload['notifications']) && is_array($payload['notifications'])) {
                        foreach ($payload['notifications'] as $n) {
                            try {
                                $sender->deliver((array)$n);
                            } catch (\Throwable $inner) {
                                Log::error('Notification deliver failed', ['event_id' => $event->id, 'error' => $inner->getMessage()]);
                                // don't throw — allow publish attempt to record and continue
                            }
                        }
                    } elseif (!empty($payload['channel'])) {
                        // single notification payload
                        $sender->deliver($payload);
                    } else {
                        // No notification: keep existing behavior of logging as published
                        Log::info('Outbox publish (no-notify)', ['id' => $event->id]);
                    }

                    $event->published = true;
                    $event->published_at = now();
                    $event->publish_attempts = $event->publish_attempts + 1;
                    $event->last_error = null;
                    $event->save();
                } catch (\Throwable $e) {
                    $event->publish_attempts = $event->publish_attempts + 1;
                    $event->last_error = (string)$e->getMessage();
                    $event->save();
                    Log::error('Failed to publish outbox event', ['id' => $event->id, 'error' => $e->getMessage()]);
                }
            }

            if ($this->option('once')) {
                break;
            }

            // brief pause to avoid tight loop in demo
            sleep(1);
        } while (true);

        $this->info('Outbox publisher finished');
        return 0;
    }
}
