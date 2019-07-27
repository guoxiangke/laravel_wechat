<?php

namespace App\Policies;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class AlbumPolicy
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
        return Gate::any(['viewAlbum', 'manageAlbum'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewAlbum', 'manageAlbum'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('manageAlbum');
    }

    public function update($user, $item)
    {
        return $user->can('manageAlbum', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('manageAlbum', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('manageAlbum', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('manageAlbum', $item);
    }
}
