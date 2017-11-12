<?php namespace Core;

use Illuminate\Routing\Router;

class AccountCest {

    public function _before(FunctionalTester $I)
    {
        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));
        });
    }

    public function canAccessUserAccount(FunctionalTester $I)
    {
        $user = $I->haveUser();

        $I->login($user->email, 'secret');

        $I->amOnPage(route('account'));
        $I->seeResponseCodeIs(200);

        $I->see('My Account', 'h1');
        $I->seeInTitle('My Account');
        $I->see($user->getPresenter()->displayName(), 'h3');
        $I->see('E-mail: ' . $user->email . ' E-mail will not be shown publicly.');
        $I->seeLink('My Account', route('account'));
        $I->seeLink('Edit Account', route('account.edit'));
        $I->seeLink('Logout', route('logout'));
        $I->seeLink('OAuth', route('account.oauth'));
    }

    public function canEditUserAccount(FunctionalTester $I)
    {
        $user = $I->haveUser();

        $I->login($user->email, 'secret');

        $I->amOnPage(route('account.edit'));
        $I->seeResponseCodeIs(200);

        $I->see('Edit Account', 'h1');
        $I->seeInTitle('Edit Account');
        $I->see('Nick name', 'label');
        $I->see('E-mail', 'label');
        $I->see('First Name', 'label');
        $I->see('Last Name', 'label');
        $I->see('New password', 'label');
        $I->see('Repeat password', 'label');
        $I->see('Save', 'button#edit-account');

        $I->seeInField('name', $user->name);
        $I->seeInField('email', $user->email);
        $I->seeInField('first_name', $user->first_name);
        $I->seeInField('last_name', $user->last_name);
        $I->seeInField('password', null);
        $I->seeInField('password_confirmation', null);
    }

    public function mustSetAnEmailToEditUserAccount(FunctionalTester $I)
    {
        $user = $I->haveUser(['email' => 'no-email']);

        $I->login('no-email', 'secret');

        $I->amOnPage(route('account.edit'));
        $I->seeResponseCodeIs(200);

        $I->see('E-mail', 'label');
        $I->see('Save', 'button#edit-account');
        $I->see('Set an email address to be able to edit your profile.', '.alert.alert-info');

        $I->seeInField('email', $user->email);
    }

    public function canAccessOAuthPage(FunctionalTester $I)
    {
        $user = $I->haveUser();

        $I->login($user->email, 'secret');

        $I->amOnPage(route('account.oauth'));
        $I->seeResponseCodeIs(200);

        $I->see('OAuth', 'h1');
        $I->seeInTitle('OAuth');
        $I->seeElement('passport-clients');
        $I->seeElement('passport-authorized-clients');
        $I->seeElement('passport-personal-access-tokens');
    }

    public function canAccessWelcomePage(FunctionalTester $I)
    {
        $user = $I->haveUser();

        $I->login($user->email, 'secret');

        session()->put('showWelcomePage', true);
        $I->amOnPage(route('account.welcome'));
        $I->seeResponseCodeIs(200);

        $I->see('Welcome', 'h1');
        $I->seeInTitle('Welcome');
        $I->see('Your account was successfully created. Thank you for your registration!');
        $I->seeLink('My Account', route('account'));
        $I->seeLink('Return to the homepage', routeMl('home'));
    }
}

