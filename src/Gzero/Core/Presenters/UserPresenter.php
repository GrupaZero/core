<?php namespace Gzero\Core\Presenters;

use Robbo\Presenter\Presenter;

class UserPresenter extends Presenter {

    /**
     * Get display name nick or first and last name
     *
     * @return string
     */
    public function displayName()
    {
        if (isset($this->name) && config('gzero.use_users_nicks')) {
            return $this->name;
        }

        if (isset($this->first_name) || isset($this->last_name)) {
            return $this->first_name . ' ' . $this->last_name;
        }

        return trans('gzero-core::common.anonymous');
    }

}
