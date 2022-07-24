<?php

declare(strict_types=1);

namespace CzechitasApp\Models;

use Carbon\Carbon;
use CzechitasApp\Models\Enums\UserRole;
use CzechitasApp\Models\Student;
use CzechitasApp\Models\StudentPayment;
use CzechitasApp\Models\Term;
use CzechitasApp\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int     $id
 * @property string  $name
 * @property string  $email
 * @property string  $password
 * @property bool    $is_blocked
 * @property string  $role
 * @property string  $access_token
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 *
 * @property Term[]|Collection $terms
 * @property Student[]|Collection $students
 * @property StudentPayment[]|Collection $authorPayments
 */
class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Notifiable, Authenticatable, Authorizable, CanResetPassword, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'is_blocked'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<string>
     */
    protected $hidden = ['password', 'remember_token', 'access_token'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_blocked' => 'boolean',
    ];

    /**
     * Students relationship builder
     */
    public function students(): Student|HasMany
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    /**
     * Author of student payments relationship builder
     */
    public function authorPayments(): StudentPayment|HasMany
    {
        return $this->hasMany(StudentPayment::class);
    }

    /**
     * Is admin or master user
     */
    public function isAdminOrMore(): bool
    {
        return $this->isRole([
            UserRole::MASTER,
            UserRole::ADMIN,
        ]);
    }

    /**
     * Is user's role the same as passed
     *
     * @param array<string>|string $role
     */
    public function isRole(array|string $role): bool
    {
        $role = Arr::wrap($role);

        return \in_array($this->getOriginal('role'), $role, true);
    }

    /**
     * Handle dynamic method calls into the model - especially isRole
     *
     * @param  string       $method
     * @param  array<mixed> $parameters
     * @return mixed
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function __call($method, $parameters)
    {
        if (\preg_match('/^isRole([A-Z][a-zA-Z]*)$/', $method, $matches)) {
            $roles = \explode('Or', $matches[1]);
            foreach ($roles as $role) {
                $role = Str::upper(Str::snake($role));
                if ($this->isRole(UserRole::getConstant($role))) {
                    return true;
                }
            }

            return false;
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
