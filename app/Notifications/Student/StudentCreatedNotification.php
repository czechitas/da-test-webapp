<?php

declare(strict_types=1);

namespace CzechitasApp\Notifications\Student;

class StudentCreatedNotification extends StudentBaseNotification
{
    protected bool $addQRPayment = true;

    /**
     * Get view path of template to render as email
     */
    protected function getTemplateView(): string
    {
        return 'mail.student.created';
    }

    /**
     * Get view path of template to render as email
     */
    protected function getSubject(): string
    {
        return \trans('students.created_mail_subject');
    }
}
