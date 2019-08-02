<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class LyLtsPolicy
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
        return Gate::any(['viewLyLts', 'manageLyLts'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewLyLts', 'manageLyLts'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('manageLyLts');
    }

    public function update($user, $item)
    {
        return $user->can('manageLyLts', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('manageLyLts', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('manageLyLts', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('manageLyLts', $item);
    }
}
