<?php

declare(strict_types=1);

namespace CzechitasApp\Models;

use CzechitasApp\Models\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int        $id
 * @property int        $student_id
 * @property string     $from
 * @property string     $to
 * @property string     $subject
 * @property string     $filename
 * @property string     $attachments
 *
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property Student $student
 */
class SendEmail extends BaseModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'attachments'   => 'array',
    ];

    /**
     * Student relationship builder
     */
    public function student(): Student|BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
