<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class PostPolicy
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
        return Gate::any(['viewPost', 'managePost'], $user);
    }

    public function view($user, $post)
    {
        return Gate::any(['viewPost', 'managePost'], $user, $post);
    }

    public function create($user)
    {
        return $user->can('managePost');
    }

    public function update($user, $post)
    {
        return $user->can('managePost', $post);
    }

    public function delete($user, $post)
    {
        return $user->can('managePost', $post);
    }

    public function restore($user, $post)
    {
        return $user->can('managePost', $post);
    }

    public function forceDelete($user, $post)
    {
        return $user->can('managePost', $post);
    }
}
