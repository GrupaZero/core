<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Models\Language;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\Validators\UserValidator;
use function resolve;

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
            ->validate('updateMe', [
                'email' => 'test@example.com',
                'name'  => 'John Doe',
            ]);
    }

    /** @test */
    public function canValidateInactiveLanguageCode()
    {
        /** @var LanguageService $languageService */

        $this->tester->haveInstance('LanguageService::class', new LanguageService(
            collect([
                new Language(['code' => 'xx', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => false, 'is_default' => false]),
            ])
        ));

        $languageService = resolve(LanguageService::class);


        $validator = $this->validator
            ->bind('name', ['user_id' => 1])
            ->bind('email', ['user_id' => 1]);

        $data = [
            'email'    => 'test@example.com',
            'name'     => 'John Doe',
            'language_code' => 'xx'
        ];

        $validator->validate('updateMe', $data);
    }
}
