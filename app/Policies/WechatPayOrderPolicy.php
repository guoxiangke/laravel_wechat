<?php

namespace App\Policies;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\HandlesAuthorization;

class WechatPayOrderPolicy
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
        return Gate::any(['viewWechatPayOrder', 'manageWechatPayOrder'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewWechatPayOrder', 'manageWechatPayOrder'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('manageWechatPayOrder');
    }

    public function update($user, $item)
    {
        return $user->can('manageWechatPayOrder', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('manageWechatPayOrder', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('manageWechatPayOrder', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('manageWechatPayOrder', $item);
    }
}
