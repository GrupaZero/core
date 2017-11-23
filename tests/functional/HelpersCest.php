<?php namespace Core;

use Gzero\Core\Models\Language;
use Gzero\Core\Services\LanguageService;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

class HelpersCest {

    public function itGeneratesStringWithMlSuffix(FunctionalTester $I)
    {
        $I->assertEquals('test-en', mlSuffix('test', 'en'));
    }

    public function itGeneratesApiUrl(FunctionalTester $I)
    {
        $I->assertEquals('http://api.dev.gzero.pl/v1/admin/users/1', apiUrl('admin/users', [1]));
    }

    public function itGeneratesSecureApiUrl(FunctionalTester $I)
    {
        $I->assertEquals('https://api.dev.gzero.pl/v1/admin/users/1', apiUrl('admin/users', [1], true));
    }

    public function itGeneratesMlUrl(FunctionalTester $I)
    {
        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false])
            ])
        ));

        $I->assertEquals(url('news'), urlMl('news', 'en'));
        $I->assertEquals(url('pl/aktualnosci'), urlMl('aktualnosci', 'pl'));

        $I->clearApplicationHandlers();
        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => false]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => true])
            ])
        ));

        $I->assertEquals(url('en/news'), urlMl('news', 'en'));
        $I->assertEquals(url('aktualnosci'), urlMl('aktualnosci' , 'pl'));
    }

    public function itGeneratesUrlToMlRoute(FunctionalTester $I)
    {
        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
                new Language(['code' => 'de', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));

            $router->get('/test', function () {
                return 'Laravel: ' . app()->getLocale();
            })->name(mlSuffix('test', $language));
        });

        // We need to visit it by url first to apply application handlers
        $I->amOnPage('/test-url');

        $I->assertEquals('home-en', mlSuffix('home'));
        $I->assertEquals('home-en', mlSuffix('home', 'en'));
        $I->assertEquals('home-it', mlSuffix('home', 'it'));

        $I->amOnPage(routeMl('test', 'en'));
        $I->seeResponseCodeIs(200);
        $I->see('Laravel: en');
        $I->amOnPage(routeMl('test'));
        $I->seeResponseCodeIs(200);
        $I->see('Laravel: en');
        $I->amOnPage(routeMl('test', 'pl'));
        $I->seeResponseCodeIs(200);
        $I->see('Laravel: pl');
        $I->amOnPage(routeMl('test', 'de'));
        $I->seeResponseCodeIs(200);
        $I->see('Laravel: de');

        $I->amOnPage(routeMl('home', 'en'));
        $I->seeResponseCodeIs(200);
        $I->see('Home: en');
        $I->amOnPage(routeMl('home', 'pl'));
        $I->seeResponseCodeIs(200);
        $I->see('Home: pl');
        $I->amOnPage(routeMl('home', 'de'));
        $I->seeResponseCodeIs(200);
        $I->see('Home: de');

        $I->getApplication()->setLocale('en'); // reset default lang after last call

        $I->amOnRoute(mlSuffix('home'));
        $I->seeResponseCodeIs(200);
        $I->see('Home: en');
        $I->amOnRoute(mlSuffix('home', 'pl'));
        $I->seeResponseCodeIs(200);
        $I->see('Home: pl');
    }

    public function itAddsMultiLanguageRoutes(FunctionalTester $I)
    {
        addMultiLanguageRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->put('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));

            $router->getRoutes()->refreshActionLookups();
            $router->getRoutes()->refreshNameLookups();
        });

        /** @var RouteCollection $routes */
        $routes  = $I->getApplication()->make('router')->getRoutes();
        $routePl = $routes->getByName(mlSuffix('home', 'pl'));
        $routeEn = $routes->getByName(mlSuffix('home', 'en'));
        $I->assertNotNull($routeEn);
        $I->assertNotNull($routePl);
        $I->assertEquals($routeEn->uri, '/');
        $I->assertEquals($routePl->uri, 'pl');
    }
}
