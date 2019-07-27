<?php

namespace App\Policies;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanSubscriptionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function viewAny($user)
    {
        return Gate::any(['viewPlanSubscription', 'managePlanSubscription'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewPlanSubscription', 'managePlanSubscription'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('managePlanSubscription');
    }

    public function update($user, $item)
    {
        return $user->can('managePlanSubscription', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('managePlanSubscription', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('managePlanSubscription', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('managePlanSubscription', $item);
    }
}
