<?php namespace Gzero\Core\Jobs;

use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\User;

class UpdateUser {

    use DBTransactionTrait;

    /** @var User */
    protected $user;

    /** @var array */
    protected $attributes;

    /**
     * Create a new job instance.
     *
     * @param User  $user       User model
     * @param array $attributes Array of attributes
     */
    public function __construct(User $user, array $attributes = [])
    {
        $this->user       = $user;
        $this->attributes = array_only($attributes, ['email', 'first_name', 'last_name', 'name', 'password']);
    }

    /**
     * Execute the job.
     *
     * @return User
     */
    public function handle()
    {
        $user = $this->dbTransaction(function () {
            if (array_key_exists('password', $this->attributes)) {
                $this->attributes['password'] = bcrypt($this->attributes['password']);
            }
            $this->user->fill($this->attributes);
            $this->user->save();
            event('user.updated', [$this->user]);
            return $this->user;
        });
        return $user;
    }

}
