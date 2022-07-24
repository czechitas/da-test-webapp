<?php

declare(strict_types=1);

namespace CzechitasApp\Policies;

use CzechitasApp\Models\Term;
use CzechitasApp\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TermPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of terms.
     */
    public function list(User $user): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can view the list of terms.
     */
    public function listParent(User $user): bool
    {
        return $user->isRoleParent();
    }

    /**
     * Determine whether the user can view the term.
     */
    public function view(User $user, Term $term): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can view the term.
     */
    public function viewParent(User $user, Term $term): bool
    {
        return $user->isRoleParent() && $term->isPossibleLogin();
    }

    /**
     * User only in admin to hide teachers contact for instructors
     */
    public function viewDetails(User $user, Term $term): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can create terms.
     */
    public function create(User $user): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can update the term.
     */
    public function update(User $user, Term $term): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can delete the term.
     */
    public function delete(User $user, Term $term): bool
    {
        if ($user->isAdminOrMore()) {
            return (int)($term->students_count ?? $term->students()->canceled(false)->count()) === 0;
        }

        return false;
    }
}
