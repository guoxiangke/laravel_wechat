<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class AlbumSubscriptionPolicy
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
        return Gate::any(['viewAlbumSubscription', 'manageAlbumSubscription'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewAlbumSubscription', 'manageAlbumSubscription'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('manageAlbumSubscription');
    }

    public function update($user, $item)
    {
        return $user->can('manageAlbumSubscription', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('manageAlbumSubscription', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('manageAlbumSubscription', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('manageAlbumSubscription', $item);
    }
}
