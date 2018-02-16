<?php namespace Gzero\Core\Jobs;

use function bcrypt;
use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\User;

class CreateUser {

    use DBTransactionTrait;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'email',
        'password',
        'name',
        'first_name',
        'last_name',
        'language_code',
        'timezone',
    ];

    /**
     * Create a new job instance.
     *
     * @param array $attributes array of attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = array_only($attributes, $this->allowedAttributes);
    }

    /**
     * Execute the job.
     *
     * @return User
     */
    public function handle()
    {
        if (empty($this->attributes['name'])) { // handle empty nickname users
            $this->attributes['name'] = $this->buildUniqueNickname();
        }
        $user = $this->dbTransaction(function () {
            if (array_key_exists('password', $this->attributes)) {
                $this->attributes['password'] = bcrypt($this->attributes['password']);
            }
            $user = new User();
            $user->fill($this->attributes);
            $user->save();
            event('user.created', [$user]);
            return $user;
        });
        return $user;
    }

    /**
     * Function returns an unique user nickname from given url in specific language
     *
     * @param string $replacement string nick replacement to use, "Anonymous" is default
     *
     * @return string $nickname an unique user nickname
     */
    protected function buildUniqueNickname($replacement = 'anonymous')
    {
        return $replacement . '-' . uniqid(User::max('id'));
    }
}
