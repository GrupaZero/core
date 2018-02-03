<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Validators\UserValidator;

class UserValidatorTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var UserValidator */
    protected $validator;

    public function _before()
    {
        $this->validator = new UserValidator(resolve('validator'));
    }

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(UserValidator::class, $this->validator);
    }

    /** @test */
    public function canValidateUsingRulesInMethod()
    {
        $this->validator
            ->bind('name', ['user_id' => 1])
            ->bind('email', ['user_id' => 1])
            ->validate('updateMe', ['email' => 'test@example.com', 'name' => 'John Doe']);
    }

}
