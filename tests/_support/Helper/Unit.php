<?php namespace Core\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\UploadableEntity;
use Gzero\Core\Models\User;

class Unit extends \Codeception\Module {
    /**
     * Create user and return entity
     *
     * @param array $attributes
     *
     * @return \Gzero\Core\Models\User
     */
    public function haveUser($attributes = [])
    {
        return factory(User::class)->create($attributes);
    }

    /**
     * Create user and return entity
     *
     * @return UploadableEntity
     */
    public function haveUploadableEntity()
    {
        return new UploadableEntity();
    }
}
