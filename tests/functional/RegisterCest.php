<?php namespace Core;

use Gzero\Core\Repositories\UserReadRepository;

class RegisterCest {


    /** @var UserReadRepository */
    protected $repository;

    public function _before(FunctionalTester $I)
    {
        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));
        });

        $this->repository = new UserReadRepository();
    }

    public function canRegisterMyself(FunctionalTester $I)
    {
        $I->amOnPage(route('register'));
        $I->seeResponseCodeIs(200);
        $I->seeInTitle(trans('gzero-core::common.register'));

        $I->fillField('email', 'john.doe@example.com');
        $I->fillField('password', 'secret');
        $I->fillField('name', 'JohnDoe');
        $I->fillField('first_name', 'John');
        $I->fillField('last_name', 'Doe');
        $I->click('button[type="submit"]');

        $I->seeResponseCodeIs(200);
        $I->amLoggedAs(['email' => 'john.doe@example.com', 'password' => 'secret'], 'web');
    }
}