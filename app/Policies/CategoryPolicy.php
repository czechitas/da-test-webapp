<?php

declare(strict_types=1);

namespace CzechitasApp\Policies;

use CzechitasApp\Models\Category;
use CzechitasApp\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of categories.
     */
    public function list(User $user): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can view the category.
     */
    public function view(User $user, Category $category): bool
    {
        return false; // Not used actually ever
    }

    /**
     * Determine whether the user can create categories.
     */
    public function create(User $user): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can update the category.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, Category $category): bool
    {
        if (!$user->isAdminOrMore()) {
            return false;
        }
        $childrenCount = $category->children_count ?? $category->children()->count();
        if ($childrenCount > 0) {
            return false;
        }
        $termsCount = $category->total_terms_count ?? $category->terms()->withTrashed()->count();

        return $termsCount <= 0;
    }
}
