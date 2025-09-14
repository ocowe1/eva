<?php
namespace Eva\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

class SendSlackNotification implements ShouldQueue
{
    use Dispatchable, Queueable;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        \Eva\Notifications\SlackNotifier::send($this->payload);
    }

    public function failed(Throwable $e)
    {
        if (function_exists('logger')) {
            logger()->error('[EVA] SendSlackNotification failed: ' . $e->getMessage());
        }
    }
}
