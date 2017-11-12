<?php namespace Gzero\Core\Jobs;

use Gzero\Core\Models\User;

class DeleteUser {

    /** @var User */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user User model
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->user->delete();
    }

}
