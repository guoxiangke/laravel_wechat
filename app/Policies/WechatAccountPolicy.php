<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class WechatAccountPolicy
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
        return Gate::any(['viewWechatAccount', 'manageWechatAccount'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewWechatAccount', 'manageWechatAccount'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('manageWechatAccount');
    }

    public function update($user, $item)
    {
        return $user->can('manageWechatAccount', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('manageWechatAccount', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('manageWechatAccount', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('manageWechatAccount', $item);
    }
}
