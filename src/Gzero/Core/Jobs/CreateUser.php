<?php namespace Gzero\Core\Jobs;

use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\User;

class CreateUser {

    use DBTransactionTrait;

    /** @var string */
    protected $email;

    /** @var string */
    protected $password;

    /** @var string */
    protected $name;

    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $languageCode;

    /** @var string */
    protected $timezone;

    /**
     * Create a new job instance.
     *
     * @param string $email        Email
     * @param string $password     Password
     * @param string $name         Name
     * @param string $firstName    First name
     * @param string $lastName     Last name
     * @param string $languageCode Preferred language
     * @param string $timezone     User's timezone
     */
    public function __construct(
        string $email,
        string $password,
        ?string $name = null,
        ?string $firstName = null,
        ?string $lastName = null,
        string $languageCode = null,
        string $timezone = null
    ) {
        $this->email        = $email;
        $this->password     = $password;
        $this->name         = $name;
        $this->firstName    = $firstName;
        $this->lastName     = $lastName;
        $this->languageCode = $languageCode;
        $this->timezone     = $timezone;
    }

    /**
     * Execute the job.
     *
     * @return User
     */
    public function handle()
    {
        if (empty($this->name)) { // handle empty nickname users
            $this->name = $this->buildUniqueNickname();
        }
        $user = $this->dbTransaction(function () {
            $user = new User();
            $user->fill([
                'email'         => $this->email,
                'password'      => bcrypt($this->password),
                'name'          => $this->name,
                'first_name'    => $this->firstName ?: null,
                'last_name'     => $this->lastName ?: null,
                'language_code' => $this->languageCode,
                'timezone'      => $this->timezone
            ]);
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
