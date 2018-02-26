<?php namespace Core;

use Carbon\Carbon;
use Gzero\Core\Events\RouteMatched;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Permission;
use Gzero\Core\Models\Role;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\User;
use Gzero\Core\Repositories\RouteReadRepository;
use Gzero\Core\Services\LanguageService;
use Illuminate\Routing\Router;
use Mockery;

class AppCest {

    public function _after()
    {
        Mockery::close();
    }

    public function applicationWorks(FunctionalTester $I)
    {
        $I->haveRoutes(function ($router) {
            /** @var Router $router */
            $router->get(
                '/',
                function () {
                    return 'Laravel';
                }
            );
        });

        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
    }

    public function itRedirectsRequestsWithIndexPHP(FunctionalTester $I)
    {
        $I->stopFollowingRedirects();

        $I->amOnPage('/index.php/test-content');

        $I->seeResponseCodeIs(301);
        $I->seeResponseContains('Redirecting to http://dev.gzero.pl/test-content');
    }

    public function itGeneratesMultiLanguageRoutesCorrectly(FunctionalTester $I)
    {
        $I->stopFollowingRedirects();

        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $languages) {
            /** @var Router $router */
            $router->get('multi-language-content', function () {
                return 'Laravel Multi Language Content: ' . app()->getLocale();
            });
        });

        $I->amOnPage('/multi-language-content');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: en');

        $I->amOnPage('/pl/multi-language-content');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: pl');

        $I->amOnPage('/en/multi-language-content');
        $I->seeResponseCodeIs(404);


        $I->clearApplicationHandlers();

        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => false]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => true]),
            ])
        ));

        $I->haveMlRoutes(function ($router) {
            /** @var Router $router */
            $router->get('test', function () {
                return 'Laravel Multi Language Content: ' . app()->getLocale();
            });
        });

        $I->haveRoutes(function ($router) {
            /** @var Router $router */
            $router->middleware('web')
                ->get('{path?}', function () {
                    return 'Dynamic Router: ' . app()->getLocale();
                })->where('path', '.*');
        });

        $I->amOnPage('/test');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: pl');

        $I->amOnPage('/en/test');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: en');

        $I->amOnPage('/pl/test');
        $I->seeResponseCodeIs(200);
        $I->see('Dynamic Router: pl');
    }

    public function itWontSetLocaleWithoutMiddlewareGroup(FunctionalTester $I)
    {
        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => false]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => true]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $langauge) {
            /** @var Router $router */
            $router->get('ml_route', function () {
                return 'Laravel Multi Language Content: ' . app()->getLocale();
            });
        });

        $I->haveRoutes(function ($router) {
            /** @var Router $router */
            $router->get('{path?}', function () {
                return 'Dynamic Router: ' . app()->getLocale();
            })->where('path', '.*');
        });


        $I->amOnPage('/ml_route');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: pl');

        $I->amOnPage('/en/ml_route');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: en');

        $I->amOnPage('/pl/test');
        $I->seeResponseCodeIs(200);
        $I->see('Dynamic Router: pl');

        $I->amOnPage('/en/test');
        $I->seeResponseCodeIs(200);
        $I->see('Dynamic Router: en');
    }

    public function canUseMultipleApplicationHandlersInSingleTest(FunctionalTester $I)
    {
        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => false]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => true]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));
        });

        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/test', function () {
                return 'Laravel: ' . app()->getLocale();
            })->name(mlSuffix('test', $language));
        });

        $I->haveRoutes(function ($router) {
            /** @var Router $router */
            $router->get('/contact', function () {
                return 'Contact';
            })->name('contact');
        });

        $I->amOnPage('/');

        $I->seeResponseCodeIs(200);
        $I->assertEquals('pl', app()->getLocale());

        $I->amOnPage('/en/test');

        $I->seeResponseCodeIs(200);
        $I->see('Laravel: en');

        $I->amOnRoute('contact');

        $I->seeResponseCodeIs(200);
        $I->see('Contact');
    }

    public function dynamicRouterWorks(FunctionalTester $I)
    {
        $route = factory(Route::class)
            ->states('makeRoutableHelloWorld')
            ->make();

        $I->haveInstance(RouteReadRepository::class, Mockery::mock(RouteReadRepository::class, [
            'getByPath' => $route,
        ]));

        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $languages) {
            /** @var Router $router */
            $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
        });


        $I->amOnPage('multi-language-content');
        $I->seeResponseCodeIs(200);
        $I->see('Hello World');
        $I->canSeeEventTriggered(RouteMatched::class);
    }

    public function dynamicRouterAllowsUserWithPermissionToViewInactiveRoute(FunctionalTester $I)
    {
        $user         = factory(User::class)->create();
        $role         = factory(Role::class)->create(['name' => 'editor']);
        $viewInactive = factory(Permission::class)->states('viewInactive')->create();
        $route        = factory(Route::class)
            ->states('inactive', 'makeRoutableHelloWorld')
            ->make();

        $user->roles()->attach($role);
        $role->permissions()->attach($viewInactive->id);

        $I->login($user->email, 'secret');

        $I->haveInstance(RouteReadRepository::class, Mockery::mock(RouteReadRepository::class, [
            'getByPath' => $route,
        ]));

        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $languages) {
            /** @var Router $router */
            $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
        });


        $I->amOnPage('multi-language-content');
        $I->seeResponseCodeIs(200);
        $I->see('Hello World');
        $I->canSeeEventTriggered(RouteMatched::class);
    }

    public function dynamicRouterDeniesAccessToInactiveRoute(FunctionalTester $I)
    {
        $route = factory(Route::class)
            ->states('inactive', 'makeRoutableHelloWorld')
            ->make(['language_code' => 'en']);

        $I->haveInstance(RouteReadRepository::class, Mockery::mock(RouteReadRepository::class, [
            'getByPath' => $route,
        ]));

        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $languages) {
            /** @var Router $router */
            $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
        });


        $I->amOnPage('multi-language-content');
        $I->seeResponseCodeIs(404);
        $I->dontSee('Hello World');
        $I->cantSeeEventTriggered(RouteMatched::class);
    }

    public function dynamicRouterDeniesAccessToInactiveRoutable(FunctionalTester $I)
    {
        $route = factory(Route::class)
            ->states('makeInactiveRoutableHelloWorld')
            ->make(['language_code' => 'en']);

        $I->haveInstance(RouteReadRepository::class, Mockery::mock(RouteReadRepository::class, [
            'getByPath' => $route,
        ]));

        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $languages) {
            /** @var Router $router */
            $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
        });


        $I->amOnPage('multi-language-content');
        $I->seeResponseCodeIs(404);
        $I->dontSee('Hello World');
        $I->cantSeeEventTriggered(RouteMatched::class);
    }

    public function carbonDatesAreTranslated(FunctionalTester $I)
    {
        $dt = Carbon::now()->subDays(2);

        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $languages) {
            /** @var Router $router */
            $router->get('multi-language-content', function () {
                return 'Check diff for humans ' . app()->getLocale() . ' translations.';
            });
        });

        $I->amOnPage('/multi-language-content');
        $I->see('Check diff for humans en translations.');
        $I->assertEquals('2 days ago', $dt->diffForHumans());

        $I->amOnPage('/pl/multi-language-content');
        $I->see('Check diff for humans pl translations.');
        $I->assertEquals('2 dni temu', $dt->diffForHumans());
    }
}
