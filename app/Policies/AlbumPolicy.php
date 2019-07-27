<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

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
