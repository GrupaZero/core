<?php namespace Gzero\Core\Models;

class GuestUser extends User {

    /**
     * @return boolean
     */
    public function isSuperAdmin()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isGuest()
    {
        return true;
    }

}
