<?php

declare(strict_types=1);

namespace CzechitasApp\Models;

use Carbon\Carbon;

/**
 * @property int        $id
 * @property string $title
 * @property string $content
 *
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 */
class News extends BaseModel
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
