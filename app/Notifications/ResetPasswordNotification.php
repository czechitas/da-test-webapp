<?php

declare(strict_types=1);

namespace CzechitasApp\Notifications;

use CzechitasApp\Notifications\BaseQueueableNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseQueueableNotification
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->queue = 'password';
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject(\mailSubject(\trans('auth.reset.subject')))
            ->markdown('mail.auth.reset_password', ['token' => $this->token]);
    }
}
