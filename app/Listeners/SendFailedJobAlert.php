<?php

namespace App\Listeners;

use App\Notifications\FailedQueueJobNotification;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendFailedJobAlert
{
    public function handle(JobFailed $event): void
    {
        $context = [
            'connection' => $event->connectionName,
            'queue' => $event->job->getQueue(),
            'job_id' => $event->job->getJobId(),
            'uuid' => $event->job->uuid(),
            'name' => $event->job->resolveName(),
            'attempts' => $event->job->attempts(),
            'exception' => $event->exception::class,
            'message' => $event->exception->getMessage(),
        ];

        Log::critical('Queue job landed in failed_jobs', $context);

        $mailTo = config('queue.failed_alerts.mail_to');

        if (! is_string($mailTo) || trim($mailTo) === '') {
            Log::warning('Failed job alert mail recipient is not configured', $context);

            return;
        }

        Notification::route('mail', $mailTo)
            ->notify(new FailedQueueJobNotification($context));
    }
}
