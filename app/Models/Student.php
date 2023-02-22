<?php

declare(strict_types=1);

namespace CzechitasApp\Models;

use Carbon\Carbon;
use CzechitasApp\Models\SendEmail;
use CzechitasApp\Models\StudentPayment;
use CzechitasApp\Models\Term;
use CzechitasApp\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $term_id
 * @property string $parent_name
 * @property string $forename
 * @property string $surname
 * @property Carbon $birthday
 * @property string $email
 * @property string|null $payment - Nullable - @see StudentPaymentType - ['transfer', 'postal_order', 'fksp', 'cash']
 * @property string $variable_symbol
 * @property string|null $logged_out - Nullable - @see StudentLogOutType - ['illness', 'other']
 * @property Carbon|null $logged_out_date - Last change of logged_out - Can be set even student is not logged out
 * @property string $alternate
 * @property string $logged_out_reason
 * @property string|null $canceled - Nullable or reason
 * @property string $restrictions
 * @property string $note
 * @property text $private_note
 *
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property-read int $total_paid Total paid
 * @property-read string $name Formatted forename and surname together
 * @property-read int $total_price Total price of course user should pay
 * @property-read int $price_to_pay Price need to be paid
 * @property-read string $payment_message Payment message with transfer/postal order
 *
 * @property StudentPayment[]|Collection $studentPayments
 * @property Term $term
 * @property User $parent
 * @property SendEmail[]|Collection $sendEmails
 *
 * @method self|Builder canceled(bool $isCancelled = true) Local scope
 * @method self|Builder loggedOut(bool $isLoggedOut = true) Local scope @link scopeLoggedOut
 * @method self|Builder withPaymentSum(string $as = 'total_paid_cache') Local with scope
 */
class Student extends BaseModel
{
    use Notifiable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthday' => 'datetime',
        'logged_out_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * CACHE Only to connect attribute and withPaymentSum
     *
     * @var int|null
     */
    protected $total_paid_cache = null;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Parent (user) relationship builder
     */
    public function parent(): User|BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Term relationship builder
     */
    public function term(): Term|BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Student payments relationship builder
     */
    public function studentPayments(): StudentPayment|HasMany
    {
        return $this->hasMany(StudentPayment::class);
    }

    /**
     * Student send emails relationship builder
     */
    public function sendEmails(): SendEmail|HasMany
    {
        return $this->hasMany(SendEmail::class);
    }

    /**
     * Route notifications for the mail channel
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    /////////////////////////////////////////////////////////
    // ------ Setters, getters and help functions -------- //
    /////////////////////////////////////////////////////////

    /**
     * Eloquent method for getting "name", ie forename and surname together as fullname
     */
    public function getNameAttribute(): string
    {
        return "{$this->forename} {$this->surname}";
    }

    /**
     * Eloquent method for getting "total_price"
     */
    public function getTotalPriceAttribute(): int
    {
        return $this->term->price;
    }

    /**
     * Eloquent method for getting "price_to_pay"
     */
    public function getPriceToPayAttribute(): int
    {
        return $this->total_price - $this->total_paid;
    }

    /**
     * Eloquent method for getting "total_paid"
     */
    public function getTotalPaidAttribute(): int
    {
        if ($this->total_paid_cache === null) {
            if (!isset($this->attributes['total_paid_cache'])) {
                return $this->loadFreshTotalPaid();
            }

            $this->total_paid_cache = (int)$this->attributes['total_paid_cache'];
        }

        return $this->total_paid_cache;
    }

    /**
     * Eloquent method for getting payment_message attribute
     */
    public function getPaymentMessageAttribute(): string
    {
        return $this->name;
    }

    ////////////////////////////////////////////////////
    // ------------------   Scopes ------------------ //
    ////////////////////////////////////////////////////

    /**
     * Register the local is student logged out scope
     *
     * @param bool $isLoggedOut Is scope valid - pass false to reverse scope
     */
    public function scopeLoggedOut(Builder $query, bool $isLoggedOut = true): Builder
    {
        if ($isLoggedOut) {
            return $query->whereNotNull('logged_out');
        }

        return $query->whereNull('logged_out');
    }

    /**
     * Register the local is student canceled scope
     *
     * @param bool $isCanceled Is scope valid - pass false to reverse scope
     */
    public function scopeCanceled(Builder $query, bool $isCanceled = true): Builder
    {
        if ($isCanceled) {
            return $query->whereNotNull('canceled');
        }

        return $query->whereNull('canceled');
    }

    /**
     * Register the withPaymentSum local scope
     */
    public function scopeWithPaymentSum(Builder $query, string $as = 'total_paid_cache'): Builder
    {
        return $query->withCount([
            'studentPayments as ' . $as => static function (Builder $query): void {
                $query->select(DB::raw('IFNULL(SUM(price), 0)'));
            },
        ]);
    }

    /////////////////////////////////////////////////////////////
    // -------------- Other helper functions ----------------- //
    /////////////////////////////////////////////////////////////

    /**
     * Load total paid data into cache from scratch
     */
    public function loadFreshTotalPaid(): int
    {
        $this->total_paid_cache = (int)$this->studentPayments()->sum('price');

        return $this->total_paid_cache;
    }

    /**
     * Check if is possible to view details of student
     */
    public function isViewable(): bool
    {
        return $this->canceled === null;
    }

    /**
     * Check if student is still editable according to config and term
     */
    public function isEditable(): bool
    {
        return $this->logged_out === null && $this->canceled === null && $this->term->areStudentsEditable();
    }

    /**
     * Check if student can be logged out of course according to config and term
     */
    public function isPossibleLogOut(): bool
    {
        return $this->logged_out === null && $this->canceled === null && $this->term->areStudentsPossibleLogOut();
    }
}
