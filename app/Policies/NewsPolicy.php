<?php

declare(strict_types=1);

namespace CzechitasApp\Policies;

use CzechitasApp\Models\News;
use CzechitasApp\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can list all news.
     */
    public function list(User $user): bool
    {
        return false;
        // return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can create news.
     */
    public function create(User $user): bool
    {
        return false;
        // return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can update the news.
     */
    public function update(User $user, News $news): bool
    {
        return false;
        // return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can delete the news.
     */
    public function delete(User $user, News $news): bool
    {
        return false;
        // return $user->isAdminOrMore();
    }
}
