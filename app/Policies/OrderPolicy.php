<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

final class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->isCreditManager() || $order->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->isSalesRepresentative();
    }

    public function update(User $user, Order $order): bool
    {
        return $user->isSalesRepresentative()
            && $order->isOwnedBy($user)
            && $order->status === OrderStatus::Draft;
    }

    public function delete(User $user, Order $order): bool
    {
        return $this->update($user, $order);
    }

    public function submit(User $user, Order $order): bool
    {
        return $user->isSalesRepresentative() && $order->isOwnedBy($user);
    }

    public function approve(User $user, Order $order): bool
    {
        return $user->isCreditManager();
    }

    public function reject(User $user, Order $order): bool
    {
        return $user->isCreditManager();
    }

    public function dispatch(User $user, Order $order): bool
    {
        return $user->isCreditManager()
            || ($user->isSalesRepresentative() && $order->isOwnedBy($user));
    }
}
