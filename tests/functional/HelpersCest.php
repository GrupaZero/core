<?php namespace Core;

use Carbon\Carbon;
use Gzero\Core\Models\Language;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\Services\OptionService;
use Gzero\Core\Services\RoutesService;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use function mlSuffix;

class HelpersCest {

    public function itGeneratesStringWithMlSuffix(FunctionalTester $I)
    {
        $I->assertEquals('test', mlSuffix('test', 'en'));
        $I->assertEquals('test-pl', mlSuffix('test', 'pl'));
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
        $I->getApplication()->instance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false])
            ])
        ));

        $I->assertEquals(url('news'), urlMl('news'));
        $I->assertEquals(url('news'), urlMl('news', 'en'));
        $I->assertEquals(url('pl/aktualnosci'), urlMl('aktualnosci', 'pl'));
        $I->assertEquals(url('aktualnosci'), urlMl('aktualnosci', 'non_existing'));

        $I->getApplication()->instance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => false]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => true])
            ])
        ));

        $I->assertEquals(url('aktualnosci'), urlMl('aktualnosci', 'pl'));
        $I->assertEquals(url('aktualnosci'), urlMl('aktualnosci'));
        $I->assertEquals(url('en/news'), urlMl('news', 'en'));
        $I->assertEquals(url('news'), urlMl('news', 'non_existing'));
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

        $I->assertEquals('home', mlSuffix('home'));
        $I->assertEquals('home', mlSuffix('home', 'en'));
        $I->assertEquals('home-pl', mlSuffix('home', 'pl'));
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
    }

    public function itGeneratesDefaultRouteForCurrentLanguage(FunctionalTester $I)
    {
        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
                new Language(['code' => 'ru', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $language) {
            $router->get('/services', function () {
                return'our services';
            })->name(mlSuffix('our-services', $language));

            $router->get('/info', function() {
                return route('our-services');
            })->name(mlSuffix('about-us', $language));
        });

        $I->amOnPage('/ru/info');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('/ru/services');
        $I->assertEquals(routeMl('our-services'), route('our-services'));
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

        $I->getApplication()->make(RoutesService::class)->registerAll(); // We need to register routers first

        /** @var RouteCollection $routes */
        $routes  = $I->getApplication()->make('router')->getRoutes();
        $routePl = $routes->getByName(mlSuffix('home', 'pl'));
        $routeEn = $routes->getByName(mlSuffix('home', 'en'));
        $I->assertNotNull($routeEn);
        $I->assertNotNull($routePl);
        $I->assertEquals($routeEn->uri, '/');
        $I->assertEquals($routePl->uri, 'pl');
    }

    public function itAddsGroupArgumentsToMultiLanguageRoute(FunctionalTester $I)
    {
        addMultiLanguageRoutes([
            'middleware' => 'web',
            'domain'     => 'test.pl'
        ], function ($router, $language) {
            /** @var Router $router */
            $router->put('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));

            $router->getRoutes()->refreshActionLookups();
            $router->getRoutes()->refreshNameLookups();
        });

        $I->getApplication()->make(RoutesService::class)->registerAll(); // We need to register routers first

        /** @var RouteCollection $routes */
        $routes = $I->getApplication()->make('router')->getRoutes();
        /** @var Route $routePl */
        /** @var Route $routeEn */
        $routePl = $routes->getByName(mlSuffix('home', 'pl'));
        $routeEn = $routes->getByName(mlSuffix('home', 'en'));
        $I->assertNotNull($routeEn);
        $I->assertNotNull($routePl);
        $I->assertEquals($routeEn->uri, '/');
        $I->assertEquals($routePl->uri, 'pl');
        $I->assertEquals($routeEn->getDomain(), 'test.pl');
        $I->assertEquals($routePl->getDomain(), 'test.pl');
        $I->assertEquals($routeEn->getPrefix(), null);
        $I->assertEquals($routePl->getPrefix(), 'pl');
    }

    public function itAddsGroupArgumentsToRoutes(FunctionalTester $I)
    {
        addRoutes([
            'middleware' => 'web',
            'domain'     => 'test.pl'
        ], function ($router) {
            /** @var Router $router */
            $router->put('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name('home');

            $router->getRoutes()->refreshActionLookups();
            $router->getRoutes()->refreshNameLookups();
        });

        $I->getApplication()->make(RoutesService::class)->registerAll(); // We need to register routers first

        /** @var RouteCollection $routes */
        $routes = $I->getApplication()->make('router')->getRoutes();
        /** @var Route $route */
        $route = $routes->getByName('home');
        $I->assertNotNull($route);
        $I->assertEquals($route->uri, '/');
        $I->assertEquals($route->getDomain(), 'test.pl');
        $I->assertEquals($route->getPrefix(), null);
    }

    public function itSetsRequestTimezoneOnApiHttpHeader(FunctionalTester $I)
    {
        // 1st case: no header set
        $I->sendGet(apiUrl('someapi'));

        $I->assertEquals('UTC', getRequestTimezone());

        // 2nd case: the http header is set
        $I->haveHttpHeader('Accept-Timezone', 'America/New_York');
        $I->sendGet(apiUrl('someapi'));

        $I->assertEquals('America/New_York', getRequestTimezone());
    }

    public function itConvertsDateTimeObjectToRequestTimezone(FunctionalTester $I)
    {
        // 1st case: no header set
        $I->sendGet(apiUrl('someapi'));

        $dateTime = Carbon::parse('2021-05-02 12:43:31', 'America/New_York');
        $dateTimeInRequestTimezone = dateTimeToRequestTimezone($dateTime);

        $I->assertEquals($dateTime, $dateTimeInRequestTimezone);
        $I->assertEquals('UTC', $dateTimeInRequestTimezone->getTimezone()->getName());

        // 2nd case: the http header is set
        $I->haveHttpHeader('Accept-Timezone', 'Australia/Adelaide');
        $I->sendGet(apiUrl('someapi'));

        $dateTime = Carbon::parse('2021-05-02 12:43:31', 'America/New_York');
        $dateTimeInRequestTimezone = dateTimeToRequestTimezone($dateTime);

        $I->assertEquals($dateTime, $dateTimeInRequestTimezone);
        $I->assertEquals('Australia/Adelaide', $dateTimeInRequestTimezone->getTimezone()->getName());
    }

    public function itConvertsDateTimeStringToRequestTimezone(FunctionalTester $I)
    {
        // 1st case: no header set
        $I->sendGet(apiUrl('someapi'));

        $dateTime = Carbon::parse('2021-05-02 12:43:31', 'America/New_York');
        $dateTimeInRequestTimezone = dateTimeToRequestTimezone($dateTime->toIso8601String());

        $I->assertEquals($dateTime, $dateTimeInRequestTimezone);
        $I->assertEquals('UTC', $dateTimeInRequestTimezone->getTimezone()->getName());

        // 2nd case: the http header is set
        $I->haveHttpHeader('Accept-Timezone', 'Australia/Adelaide');
        $I->sendGet(apiUrl('someapi'));

        $dateTime = Carbon::parse('2021-05-02 12:43:31', 'America/New_York');
        $dateTimeInRequestTimezone = dateTimeToRequestTimezone($dateTime->toIso8601String());

        $I->assertEquals($dateTime, $dateTimeInRequestTimezone);
        $I->assertEquals('Australia/Adelaide', $dateTimeInRequestTimezone->getTimezone()->getName());
    }

    public function optionFallbackToDefaultInAllNullCases(FunctionalTester $I)
    {
        $option = $I->haveOption([
            'category_key' => 'general',
            'key' => 'someoption',
            'value' => ["en" => "english value", "pl" => null]
        ]);

        $I->assertEquals("english value", option('general', 'someoption', 'xxx'));
        $I->assertEquals("english value", option('general', 'someoption', 'xxx', 'en'));

        $I->assertEquals("xxx", option('general', 'someoption', 'xxx', 'pl'));
        $I->assertEquals("xxx", option('general', 'someoption', 'xxx', 'fr'));
    }
}
