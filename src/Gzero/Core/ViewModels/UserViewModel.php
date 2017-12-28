<?php namespace Gzero\Core\ViewModels;

class UserViewModel {

    /** @var array */
    protected $data;

    /** @var array */
    protected $allowedAttributes = [
        'id',
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'has_social_integrations',
        'is_admin'
    ];

    /**
     * UserViewModel constructor.
     *
     * @param array $data Array with data
     */
    public function __construct(array $data)
    {
        $this->data = array_only($data, $this->allowedAttributes);
    }

    /**
     * @return string
     */
    public function name()
    {
        return array_get($this->data, 'name');
    }

    /**
     * @return string
     */
    public function email()
    {
        return array_get($this->data, 'email');
    }

    /**
     * @return string
     */
    public function firstName()
    {
        return array_get($this->data, 'first_name');
    }

    /**
     * @return string
     */
    public function lastName()
    {
        return array_get($this->data, 'last_name');
    }

    /**
     * @return bool
     */
    public function password()
    {
        return !!array_get($this->data, 'password');
    }

    /**
     * @return bool
     */
    public function hasSocialIntegrations()
    {
        return array_get($this->data, 'has_social_integrations', false);
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return array_get($this->data, 'is_admin', false);
    }

    /**
     * Get display name nick or first and last name
     *
     * @return string
     */
    public function displayName()
    {
        if (isset($this->data['name']) && config('gzero.use_users_nicks')) {
            return $this->data['name'];
        }

        if (isset($this->data['first_name']) || isset($this->data['last_name'])) {
            return $this->data['first_name'] . ' ' . $this->data['last_name'];
        }

        return trans('gzero-core::common.anonymous');
    }

}
