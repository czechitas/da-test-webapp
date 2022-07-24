<?php

declare(strict_types=1);

namespace CzechitasApp\Models;

use Carbon\Carbon;
use CzechitasApp\Models\Student;
use CzechitasApp\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $student_id
 * @property int $price
 * @property string $payment - @see \CzechitasApp\Models\Enums\StudentPaymentType
 *                           ['transfer', 'postal_order', 'fksp', 'cash']
 * @property string $note
 * @property int $user_id
 * @property ?Carbon $received_at
 *
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property Student $student
 * @property User $user
 */
class StudentPayment extends BaseModel
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'received_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Student relationship builder
     */
    public function student(): Student|BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * User (author) relationship builder
     */
    public function user(): User|BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isInsertedBackward(): bool
    {
        return $this->received_at->diffInSeconds($this->created_at, true) > 10;
    }
}
