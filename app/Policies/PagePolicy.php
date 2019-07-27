<?php

namespace App\Policies;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
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
        return Gate::any(['viewPage', 'managePage'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewPage', 'managePage'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('managePage');
    }

    public function update($user, $item)
    {
        return $user->can('managePage', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('managePage', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('managePage', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('managePage', $item);
    }
}
