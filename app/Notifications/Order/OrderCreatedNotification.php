<?php

declare(strict_types=1);

namespace CzechitasApp\Notifications\Order;

use CzechitasApp\Models\Order;
use CzechitasApp\Notifications\BaseQueueableNotification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCreatedNotification extends BaseQueueableNotification
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject(\mailSubject(\trans('orders.subject.created')))
            ->markdown('mail.order.created', ['order' => $this->order]);
    }
}
