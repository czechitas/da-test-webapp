<?php

declare(strict_types=1);

namespace CzechitasApp\Notifications\Student;

use CzechitasApp\Models\Student;

class StudentAdminUpdatedNotification extends StudentBaseNotification
{
    protected string $action;

    public function __construct(Student $student, string $action)
    {
        parent::__construct($student);

        $this->action = $action;
    }

    /**
     * Get view path of template to render as email
     */
    protected function getTemplateView(): string
    {
        return 'mail.student.admin_updated';
    }

    /**
     * Get view path of template to render as email
     */
    protected function getSubject(): string
    {
        return \trans('students.admin_update.mail_subject');
    }

    /**
     * Get data to mail
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        return \array_merge(parent::getData(), [
            'action'    => $this->action,
        ]);
    }
}
