<?php

declare(strict_types=1);

namespace CzechitasApp\Policies;

use CzechitasApp\Models\Order;
use CzechitasApp\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of orders.
     */
    public function list(User $user): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the order.
     */
    public function updateFlag(User $user, Order $order): bool
    {
        return $user->isAdminOrMore();
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdminOrMore();// && !$order->isSigned();
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdminOrMore();// && !$order->isSigned();
    }
}
