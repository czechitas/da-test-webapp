<?php

declare(strict_types=1);

namespace CzechitasApp\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * @property int        $id
 * @property string     $flag
 * @property ?Carbon    $signature_date
 * @property string     $type ['camp', 'school_nature']
 * @property string     $client
 * @property string     $address
 * @property string     $ico
 * @property string     $substitute
 * @property string     $contact_name
 * @property string     $contact_tel
 * @property string     $contact_mail
 * @property Carbon     $start_date_1
 * @property Carbon     $start_date_2
 * @property Carbon     $start_date_3
 * @property Carbon     $final_date_from
 * @property Carbon     $final_date_to
 * @property array      $xdata
 *           ## Camp
 *           [date_part, students, age, adults, end_date_1, end_date_2, end_date_3]
 *           ## School nature
 *           [students, age, adults, start_time, start_food, end_time, end_food]
 *           ## Admin
 *           [price_kid, price_adult]
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @method self|Builder notSigned() Get notSigned order scope
 */
class Order extends BaseModel
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'signature_date',
        'start_date_1',
        'start_date_2',
        'start_date_3',
        'final_date_from',
        'final_date_to',

        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'xdata'             => 'array',
    ];

    /**
     * The xdata attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $xdataCasts = [
        'students'      => 'integer',
        'adulst'        => 'integer',
        'price_kid'     => 'integer',
        'price_adult'   => 'integer',
        'end_date_1'    => 'date',
        'end_date_2'    => 'date',
        'end_date_3'    => 'date',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->contact_mail;
    }

    /**
     * Is order filled by admins
     */
    public function isFilled(): bool
    {
        return $this->final_date_to !== null;
    }

    /**
     * Order is signed - alternative to @see scopeNotSigned
     */
    public function isSigned(): bool
    {
        return $this->signature_date !== null;
    }

    /**
     * Register the local not signed orders scope
     */
    public function scopeNotSigned(Builder $query): Builder
    {
        return $query->whereNull('signature_date');
    }

    /**
     * Get extra data encoded in JSON xdata column
     *
     * @param  mixed $key
     * @return mixed
     */
    public function getXData($key)
    {
        if (!isset($this->xdata[$key])) {
            return null;
        }
        if (!isset($this->xdataCasts[$key])) {
            return $this->xdata[$key];
        }
        switch ($this->xdataCasts[$key]) {
            case 'int':
            case 'integer':
                return \intval($this->xdata[$key]);
            case 'date':
                $date = $this->xdata[$key];
                if ($date instanceof Carbon) {
                    return $date;
                }
                if (\is_array($date)) {
                    return Carbon::__set_state($date);
                }

                return (new Carbon($date))->setTimezone(\config('app.timezone'));
            default:
                return $this->xdata[$key];
        }
    }

    /**
     * Eloquent method for getting total_price without transfer attribute of Order
     */
    public function getTotalPriceAttribute(): int
    {
        $totalPrice = 0;
        if ($this->getXData('price_kid') > 0) {
            $totalPrice += $this->getXData('students') * $this->getXData('price_kid');
            if ($this->getXData('adults') > 0) {
                $totalPrice += $this->getXData('adults') * $this->getXData('price_adult');
            }
        }

        return $totalPrice;
    }

    /**
     * Eloquent method for getting date "name", ie final_date_from and final_date_to date formatted
     */
    public function getTermRangeAttribute(): string
    {
        if ($this->final_date_from->diffInDays($this->final_date_to) === 0) {
            return $this->final_date_from->format('d.m.Y');
        }
        $final_date_from = $this->final_date_from->format('d.m.');
        if ($this->final_date_from->year !== $this->final_date_to->year) {
            $final_date_from .= $this->final_date_from->year;
        }
        $final_date_to = $this->final_date_to->format('d.m.Y');

        return "{$final_date_from} - {$final_date_to}";
    }
}
