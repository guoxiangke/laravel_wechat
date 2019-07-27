<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class LyMetaPolicy
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
        return Gate::any(['viewLyMeta', 'manageLyMeta'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewLyMeta', 'manageLyMeta'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('manageLyMeta');
    }

    public function update($user, $item)
    {
        return $user->can('manageLyMeta', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('manageLyMeta', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('manageLyMeta', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('manageLyMeta', $item);
    }
}
