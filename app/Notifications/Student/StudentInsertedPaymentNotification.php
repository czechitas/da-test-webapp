<?php

declare(strict_types=1);

namespace CzechitasApp\Notifications\Student;

use CzechitasApp\Models\Student;

class StudentInsertedPaymentNotification extends StudentBaseNotification
{
    protected float $price;

    public function __construct(Student $student, float $price)
    {
        parent::__construct($student);

        $this->price = $price;
    }

    /**
     * Get view path of template to render as email
     */
    protected function getTemplateView(): string
    {
        return 'mail.student.inserted_payment';
    }

    /**
     * Get view path of template to render as email
     */
    protected function getSubject(): string
    {
        return \trans('students.payments.subject');
    }

    /**
     * Get data to mail
     *
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        return \array_merge(parent::getData(), [
            'price' => $this->price,
        ]);
    }
}
