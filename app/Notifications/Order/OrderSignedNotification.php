<?php

declare(strict_types=1);

namespace CzechitasApp\Notifications\Order;

use CzechitasApp\Models\Order;
use CzechitasApp\Notifications\BaseQueueableNotification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderSignedNotification extends BaseQueueableNotification
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
        $message = (new MailMessage())
            ->subject(\mailSubject(\trans('orders.subject.signed')))
            ->markdown('mail.order.signed', ['order' => $this->order]);

        return $message;
    }
}
