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

        $I->sendPOST(route('post.register'), [
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            //'name'       => '',
            //'first_name' => 'John',
            //'last_name'  => 'Doe',
            '_token'     => csrf_token(),
            //'language_code' => 'pl',
            //'timezone'      => 'Aftica/Algiers'
        ]);

        //$userFromDb = $this->repository->getById(2);

        $I->seeResponseCodeIs(200);
        //$I->amLoggedAs(['email' => 'john.doe@example.com', 'password' => 'secret'], 'web');
        //
        //
        //$I->login('john.doe@example.com', 'secret');
    }
}