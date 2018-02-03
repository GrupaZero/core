<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Models\Language;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\Validators\UserValidator;
use Illuminate\Validation\ValidationException;
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
    public function canValidateNonexistentLanguageCode()
    {
        $this->tester->getApplication()->instance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => false, 'is_default' => false]),
            ])
        ));

        $validator = $this->validator
            ->bind('name', ['user_id' => 1])
            ->bind('email', ['user_id' => 1]);

        $data = [
            'email'    => 'test@example.com',
            'name'     => 'John Doe',
            'language_code' => 'xx'
        ];

        try {
            $validator->validate('updateMe', $data);
        } catch (ValidationException $exception) {
            $this->assertNotSame(
                false,
                array_search('This language is not active', $exception->errors()['language_code']));
            return;
        }

        $this->fail('It should throw an exception');
    }

    /** @test */
    public function canValidateInactiveLanguageCode()
    {
        $this->tester->getApplication()->instance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => false, 'is_default' => false]),
            ])
        ));

        $validator = $this->validator
            ->bind('name', ['user_id' => 1])
            ->bind('email', ['user_id' => 1]);

        $data = [
            'email'    => 'test@example.com',
            'name'     => 'John Doe',
            'language_code' => 'pl'
        ];

        try {
            $validator->validate('updateMe', $data);
        } catch (ValidationException $exception) {
            $this->assertNotSame(
                false,
                array_search(trans('gzero-core::user.invalid_language'), $exception->errors()['language_code']));
            return;
        }

        $this->fail('It should throw an exception');
    }

    /** @test */
    public function canValidateActiveLanguageCode()
    {
        $this->tester->getApplication()->instance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => false, 'is_default' => false]),
            ])
        ));

        $validator = $this->validator
            ->bind('name', ['user_id' => 1])
            ->bind('email', ['user_id' => 1]);

        $data = [
            'email'    => 'test@example.com',
            'name'     => 'John Doe',
            'language_code' => 'en'
        ];

        $validator->validate('updateMe', $data);
    }
}
