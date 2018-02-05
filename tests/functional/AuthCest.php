<?php namespace Core;

use App\User;
use Gzero\Core\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Routing\Router;
use Illuminate\Support\Testing\Fakes\NotificationFake;

class AuthCest {

    public function _before(FunctionalTester $I)
    {
        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));
        });
    }

    public function canAccessLoginPage(FunctionalTester $I)
    {
        $I->amOnPage(route('login'));
        $I->seeResponseCodeIs(200);

        $I->see('Login', 'h1');
        $I->seeInTitle('Login');
        $I->see('E-mail', 'label');
        $I->see('Password', 'label');
        $I->see('Remember me', 'label');

        $I->seeInField('email', null);
        $I->seeInField('password', null);
        $I->see('Login', 'button[type=submit]');

        $I->seeLink('Forgot password?', route('password.request'));
        $I->seeLink('Register', route('register'));
    }

    public function canAccessRegisterPage(FunctionalTester $I)
    {
        $I->amOnPage(route('register'));
        $I->seeResponseCodeIs(200);

        $I->see('Register', 'h1');
        $I->seeInTitle('Register');
        $I->see('E-mail', 'label');
        $I->see('Nick name', 'label');
        $I->see('First Name', 'label');
        $I->see('Last Name', 'label');
        $I->see('Password', 'label');

        $I->seeInField('email', null);
        $I->seeInField('name', null);
        $I->seeInField('first_name', null);
        $I->seeInField('last_name', null);
        $I->seeInField('password', null);
        $I->see('Register', 'button[type=submit]');
    }

    public function canAccessForgotPasswordPage(FunctionalTester $I)
    {
        $I->amOnPage(route('password.request'));
        $I->seeResponseCodeIs(200);

        $I->see('Reset Password', 'h1');
        $I->seeInTitle('Reset Password');
        $I->see('E-mail', 'label');

        $I->seeInField('email', null);
        $I->see('Send password reset link', 'button[type=submit]');
    }

    public function ItShouldUseResetPasswordNotificationWhenResetForgottenPassword(FunctionalTester $I)
    {
        $user = $I->haveUser();

        $I->getApplication();

        $fake = new NotificationFake;

        $I->haveInstance(ChannelManager::class, $fake);

        $I->amOnPage(route('password.request'));
        $I->fillField(['id' => 'email'], $user->email);
        $I->click('button[type=submit]');

        if (class_exists('App\Models\User')) {
            $user = \App\Models\User::find($user->id); // We need App\Models\User for assertions if test runs in platform
        } else {
            $user = User::find($user->id); // We need App\User for assertions if test runs alone in core
        }

        $fake->assertSentTo($user, ResetPasswordNotification::class);
        $fake->assertSentToTimes($user, ResetPasswordNotification::class, 1);
    }

    public function canAccessPasswordResetPage(FunctionalTester $I)
    {
        $I->amOnPage(route('password.reset', ['token' => 'reset-token']));
        $I->seeResponseCodeIs(200);

        $I->see('Reset Password', 'h1');
        $I->seeInTitle('Reset Password');
        $I->see('E-mail', 'label');
        $I->see('Password', 'label');
        $I->see('Confirm Password', 'label');

        $I->seeInField('email', null);
        $I->seeInField('password', null);
        $I->seeInField('password_confirmation', null);
        $I->see('Reset Password', 'button[type=submit]');
    }

    public function canAccessLogoutPage(FunctionalTester $I)
    {
        $user = $I->haveUser();

        $I->login($user->email, 'secret');
        $I->amOnPage(route('logout'));
        $I->seeResponseCodeIs(200);

        $I->dontSeeAuthentication();
    }
}

