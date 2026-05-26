<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OutboxEvent;
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
                    // Simulate publishing: replace with real broker integration
                    $this->info('Publishing event id=' . $event->id . ' type=' . $event->event_type);
                    Log::info('Outbox publish', ['id' => $event->id, 'event' => $event->toArray()]);

                    $event->published = true;
                    $event->published_at = now();
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
