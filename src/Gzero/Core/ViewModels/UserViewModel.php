<?php namespace Gzero\Core\ViewModels;

class UserViewModel {

    /** @var array */
    protected $data;

    /** @var array */
    protected $allowedAttributes = [
        'id',
        'name',
        'email',
        'hasValidPassword',
        'first_name',
        'last_name',
        'has_social_integrations',
        'is_admin',
        'language_code',
        'timezone'
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
    public function hasValidPassword()
    {
        return !!array_get($this->data, 'hasValidPassword');
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
     * @return string
     */
    public function languageCode()
    {
        return array_get($this->data, 'language_code');
    }

    /**
     * @return string
     */
    public function timezone()
    {
        return array_get($this->data, 'timezone');
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
