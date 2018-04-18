<?php namespace Core;

use Illuminate\View\Compilers\BladeCompiler;

class TimezoneCest {

    public function _before(FunctionalTester $I)
    {
        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));
        });
    }

    public function nonLoggedGetsTimezoneFromConfig(FunctionalTester $I)
    {
        config(['app.timezone' => 'Europe/Warsaw']);
        $I->sendGet('/');
        $I->seeResponseCodeIs(200);
        $I->assertEquals('Europe/Warsaw', getTimezone());

    }

    public function loggedGetsHisTimezone(FunctionalTester $I)
    {
        config(['app.timezone' => 'Europe/Warsaw']);
        $user = $I->haveUser(['timezone' => 'America/NewYork']);

        $I->login($user->email, 'secret');

        $I->amOnPage(route('account'));
        $I->seeResponseCodeIs(200);
        $I->assertEquals('America/NewYork', getTimezone());
    }

    public function loggedWithoutTimezoneSavedGetsTimezoneFromConfig(FunctionalTester $I)
    {
        config(['app.timezone' => 'Europe/Warsaw']);
        $user = $I->haveUser();

        $I->login($user->email, 'secret');

        $I->amOnPage(route('account'));
        $I->seeResponseCodeIs(200);

        $I->assertEquals('Europe/Warsaw', getTimezone());
    }

    public function apiCallFromNonLoggedGetsTimezoneFromConfigByDefault(FunctionalTester $I)
    {
        config(['app.timezone' => 'Europe/Warsaw']);

        $I->sendGet(apiUrl('languages'));
        $I->seeResponseCodeIs(200);

        $I->assertEquals('Europe/Warsaw', getTimezone());
    }

    public function apiCallFromLoggedGetsTimezoneFromConfigByDefault(FunctionalTester $I)
    {
        $user = $I->haveUser(['timezone' => 'America/Los_Angeles']);
        config(['app.timezone' => 'Europe/Warsaw']);

        $I->login($user->email, 'secret');
        $I->sendGet(apiUrl('languages'));
        $I->seeResponseCodeIs(200);

        $I->assertEquals('Europe/Warsaw', getTimezone());
    }

    public function apiCallFromNonLoggedGetsTimezoneFromHeaderWhenPresent(FunctionalTester $I)
    {
        config(['app.timezone' => 'Europe/Warsaw']);
        $I->haveHttpHeader('Accept-Timezone', 'America/NewYork');

        $I->sendGet(apiUrl('languages'));
        $I->seeResponseCodeIs(200);

        $I->assertEquals('America/NewYork', getTimezone());
    }

    public function apiCallFromLoggedGetsTimezoneFromHeaderWhenPresent(FunctionalTester $I)
    {
        config(['app.timezone' => 'Europe/Warsaw']);
        $I->haveHttpHeader('Accept-Timezone', 'America/NewYork');

        $user = $I->haveUser(['timezone' => 'America/Los_Angeles']);
        $I->login($user->email, 'secret');
        $I->sendGet(apiUrl('languages'));
        $I->seeResponseCodeIs(200);

        $I->assertEquals('America/NewYork', getTimezone());
    }
}
