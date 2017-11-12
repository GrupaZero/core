<?php namespace Gzero\Core\Policies;

use Gzero\Core\Models\User;

class RoutePolicy {

    /**
     * Policy for viewing single route when inactive
     *
     * @param User $user User trying to do it
     *
     * @return boolean
     */
    public function viewInactive(User $user)
    {
        return $user->hasPermission('view-inactive-route');
    }
}
