<?php

declare(strict_types=1);

namespace CzechitasApp\Models;

use Carbon\Carbon;
use CzechitasApp\Models\Category;
use CzechitasApp\Models\Student;
use CzechitasApp\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $category_id
 * @property string $flag
 * @property Carbon $start
 * @property Carbon $end
 * @property ?Carbon $opening
 * @property int $price
 * @property string $note_public
 * @property string $note_private
 *
 * @property Carbon     $created_at
 * @property Carbon     $updated_at
 *
 * @property-read string $term_range Formatted start and end date together
 * @property-read bool $select_payment Should parent select type of payment
 *
 * @property User $admin
 * @property Category $category
 * @property Student[]|Collection $students
 *
 * @method self|Builder possibleLogin()
 * @method self|Builder possibleAdminLogin()
 */
class Term extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'opening',
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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start'             => 'date',
        'end'               => 'date',
    ];

    /**
     * Term admin relationship builder
     */
    public function admin(): User|BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category relationship builder
     */
    public function category(): Category|BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Students relationship builder
     */
    public function students(): Student|HasMany
    {
        return $this->hasMany(Student::class);
    }

    /////////////////////////////////////////////////////////
    // ------ Setters, getters and help functions -------- //
    /////////////////////////////////////////////////////////

    /**
     * Eloquent method for getting date "name", ie start and end date formatted
     */
    public function getTermRangeAttribute(): string
    {
        if ($this->start->diffInDays($this->end) === 0) {
            $termRange = $this->start->format('d.m.Y');
        } else {
            $start = $this->start->format('d.m.');
            if ($this->start->year !== $this->end->year) {
                $start .= $this->start->year;
            }
            $end = $this->end->format('d.m.Y');
            $termRange = "{$start} - {$end}";
        }

        return $termRange;
    }

    public function getSelectPaymentAttribute(): bool
    {
        return true;
    }

    ////////////////////////////////////////////////////
    // ------------------   Scopes ------------------ //
    ////////////////////////////////////////////////////

    /**
     * Register the local possibleLogin term scope
     * Must be in sync with @see isPossibleLogin
     */
    public function scopePossibleLogin(Builder $query): Builder
    {
        $deadline = Carbon::now()
            ->startOfDay()
            ->addDays(\config('czechitas.student.login_before_start'))
            ->format('Y-m-d');

        return $query->where('start', '>=', $deadline)
            ->where(static function (Builder $query): void {
                /** @var Builder<Term> $query */
                $query->whereNull('opening')
                    ->orWhere('opening', '<=', Carbon::now());
            });
    }

    /**
     * Register the local possibleAdminLogin term scope
     */
    public function scopePossibleAdminLogin(Builder $query): Builder
    {
        // Must be in sync with @see isPossibleAdminChangeTerm

        $deadline = Carbon::now()
            ->startOfDay()
            ->format('Y-m-d');

        return $query->where('end', '>=', $deadline);
    }

    /////////////////////////////////////////////////////////////
    // -------------- Other helper functions ----------------- //
    /////////////////////////////////////////////////////////////

    /**
     * Check if student is still editable according to config and term
     */
    public function isPossibleAdminChangeTerm(): bool
    {
        // Must be in sync with scopePossibleAdminLogin
        return Carbon::now()
            ->startOfDay()
            ->lte($this->end);
    }

    /**
     * Check if student is still editable according to config and term
     * Must be in sync with @see scopePossibleLogin
     */
    public function isPossibleLogin(): bool
    {
        $isBeforeStart = Carbon::now()
            ->startOfDay()
            ->addDays(\config('czechitas.student.login_before_start'))
            ->lte($this->start);

        return $isBeforeStart && ($this->opening === null || Carbon::now()->gt($this->opening));
    }

    /**
     * Check if student is still editable according to config and term
     */
    public function areStudentsEditable(): bool
    {
        return Carbon::now()
            ->startOfDay()
            ->addDays(\config('czechitas.student.edit_before_start'))
            ->lte($this->start);
    }

    /**
     * Check if student can be logged out of course according to config and term
     */
    public function areStudentsPossibleLogOut(): bool
    {
        return Carbon::now()
            ->startOfDay()
            ->addDays(\config('czechitas.student.logout_before_end'))
            ->lte($this->end);
    }
}
