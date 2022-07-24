<?php

declare(strict_types=1);

namespace CzechitasApp\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class BaseQueueableNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /** @var int Retry after 180s to prevent errors when Mailgun is down */
    public int $backoff = 180;
}
