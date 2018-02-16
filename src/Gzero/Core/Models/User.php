<?php namespace Gzero\Core\Models;

use Gzero\Core\ViewModels\UserViewModel;
use Gzero\Core\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Robbo\Presenter\PresentableInterface;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    PresentableInterface {

    use Authenticatable, Authorizable, CanResetPassword, HasApiTokens;

    /**
     * @var array
     */
    protected $fillable = [
        'email',
        'first_name',
        'has_social_integrations',
        'last_name',
        'name',
        'password',
        'remember_token',
        'language_code',
        'timezone'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_admin' => false
    ];

    /**
     * Permission map
     *
     * @var array
     */
    protected $permissionsMap = null;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    /*
    |--------------------------------------------------------------------------
    | START override methods of CanResetPassword trait
    |--------------------------------------------------------------------------
    */

    /**
     * Send the password reset notification.
     *
     * @param  string $token Token required to rest password
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /*
    |--------------------------------------------------------------------------
    | END override methods of CanResetPassword trait
    |--------------------------------------------------------------------------
    */

    /**
     * The roles that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'acl_user_role')->withTimestamps();
    }

    /**
     * Checks is user have super admin permissions
     *
     * @return boolean
     */
    public function isSuperAdmin()
    {
        return (boolean) $this->is_admin;
    }

    /**
     * Only GuestUser should have it set to true
     *
     * @return boolean
     */
    public function isGuest()
    {
        return false;
    }

    /**
     * It checks if given user have specified permission
     *
     * @param string $permission Permission name
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (!is_array($this->permissionsMap)) {
            $permissionsMap = cache()->get('permissions:' . $this->id, null);
            if ($permissionsMap === null) { // Not in cache
                $this->permissionsMap = $this->buildPermissionsMap();
                cache()->forever('permissions:' . $this->id, $this->permissionsMap);
            } else {
                $this->permissionsMap = $permissionsMap;
            }
        }
        return in_array($permission, $this->permissionsMap);
    }

    /**
     * Return a created presenter.
     *
     * @return UserViewModel
     */
    public function getPresenter()
    {
        return new UserViewModel($this->toArray());
    }

    /**
     * It build permission map.
     * Later we store this map cache.
     *
     * @return array
     */
    private function buildPermissionsMap()
    {
        $permissionsMap = [];
        $roles          = $this->roles()->with('permissions')->get()->toArray();
        foreach ($roles as $role) {
            if (!empty($role['permissions'])) {
                foreach ($role['permissions'] as $permission) {
                    $permissionsMap[] = $permission['name'];
                }
            }
        }
        return array_unique($permissionsMap);
    }
}
