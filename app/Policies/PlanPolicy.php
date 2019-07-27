<?php

namespace App\Policies;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanPolicy
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
        return Gate::any(['viewPlan', 'managePlan'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewPlan', 'managePlan'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('managePlan');
    }

    public function update($user, $item)
    {
        return $user->can('managePlan', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('managePlan', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('managePlan', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('managePlan', $item);
    }
}
