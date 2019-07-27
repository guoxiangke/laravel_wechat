<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class WechatAutoReplyPolicy
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
        return Gate::any(['viewWechatAutoReply', 'manageWechatAutoReply'], $user);
    }

    public function view($user, $item)
    {
        return Gate::any(['viewWechatAutoReply', 'manageWechatAutoReply'], $user, $item);
    }

    public function create($user)
    {
        return $user->can('manageWechatAutoReply');
    }

    public function update($user, $item)
    {
        return $user->can('manageWechatAutoReply', $item);
    }

    public function delete($user, $item)
    {
        return $user->can('manageWechatAutoReply', $item);
    }

    public function restore($user, $item)
    {
        return $user->can('manageWechatAutoReply', $item);
    }

    public function forceDelete($user, $item)
    {
        return $user->can('manageWechatAutoReply', $item);
    }
}
