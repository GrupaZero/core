<?php namespace Gzero\Core;

use Carbon\Carbon;
use Gzero\Core\Http\Middleware\Init;
use Gzero\Core\Http\Middleware\MultiLanguage;
use Gzero\Core\Http\Middleware\ViewShareUser;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Option;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\User;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\Services\OptionService;
use Gzero\Core\Policies\OptionPolicy;
use Gzero\Core\Policies\UserPolicy;
use Gzero\Core\Policies\RoutePolicy;
use Gzero\DomainException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Routing\Router;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;
use Laravel\Passport\Passport;
use Robbo\Presenter\PresenterServiceProvider;

class ServiceProvider extends AbstractServiceProvider {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [
        PresenterServiceProvider::class
    ];

    /**
     * List of service providers aliases
     *
     * @var array
     */
    protected $aliases = [
        'options' => OptionService::class
    ];

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Route::class  => RoutePolicy::class,
        User::class   => UserPolicy::class,
        Option::class => OptionPolicy::class
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->mergeConfig();
        $this->registerHelpers();
        $this->bindRepositories();
        $this->bindOtherStuff();
        if (config('app.env') !== 'testing') { // We're manually registering it for test cases
            $this->app->booted(function () {
                addMultiLanguageRoutes(function ($router) {
                    $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
                });
            });
        }
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->setDefaultLocale();

        $this->registerRoutePatterns();
        $this->registerRoutes();

        /** @TODO Probably we can move this to routes file */
        Passport::routes();

        Passport::tokensExpireIn(Carbon::now()->addDays(15));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));

        Resource::withoutWrapping();

        $this->registerPolicies();
        $this->registerMigrations();
        $this->registerFactories();
        $this->registerMiddleware();
        $this->registerViews();
        $this->registerPublishes();
        $this->registerTranslations();
    }

    /**
     * It registers default locale
     *
     * @throws DomainException
     *
     * @return Language default language
     */
    public static function setDefaultLocale()
    {
        $defaultLanguage = resolve(LanguageService::class)->getDefault();
        if (empty($defaultLanguage)) {
            throw new DomainException('No default language found');
        }

        app()->setLocale($defaultLanguage->code);

        return $defaultLanguage;
    }

    /**
     * Bind services
     *
     * @return void
     */
    protected function bindRepositories()
    {
        if ($this->app->runningInConsole() && config('app.env') !== 'testing') {
            $this->app->singleton(
                LanguageService::class,
                function () {
                    return new LanguageService(
                        collect([new Language(['code' => app()->getLocale(), 'is_enabled' => true, 'is_default' => true])])
                    );
                }
            );
        } else {
            $this->app->singleton(
                LanguageService::class,
                function () {
                    return new LanguageService(
                        cache()->rememberForever('languages', function () {
                            return Language::all();
                        })
                    );
                }
            );
        }

        //$this->app->singleton(
        //    'gzero.menu.account',
        //    function () {
        //        return new Register();
        //    }
        //);
        //
        //$this->app->singleton(
        //    'croppa.src_dir',
        //    function () {
        //        return resolve('filesystem')->disk(config('gzero.upload.disk'))->getDriver();
        //    }
        //);
    }

    /**
     * Register polices
     *
     * @return void
     */
    protected function registerPolicies()
    {
        $gate = resolve(Gate::class);
        $gate->before(
            function ($user) {
                if ($user->isSuperAdmin()) {
                    return true;
                }

                if ($user->isGuest()) {
                    return false;
                }
            }
        );
        foreach ($this->policies as $key => $value) {
            $gate->policy($key, $value);
        }
    }

    /**
     * Bind other services
     *
     * @return void
     */
    protected function bindOtherStuff()
    {
        //
    }

    /**
     * Add additional file to store helpers
     *
     * @return void
     */
    protected function registerHelpers()
    {
        require __DIR__ . '/helpers.php';
    }

    /**
     * It registers gzero config
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/config.php',
            'gzero'
        );
    }

    /**
     * It registers db migrations
     *
     * @return void
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
    }

    /**
     * It registers factories
     *
     * @return void
     */
    protected function registerFactories()
    {
        resolve(Factory::class)->load(__DIR__ . '/../../../database/factories');
    }

    /**
     * It register all middleware
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        resolve(Kernel::class)->prependMiddleware(Init::class);
        /** @var Router $router */
        $router = resolve(Router::class);
        $router->prependMiddlewareToGroup('web', MultiLanguage::class);
        $router->pushMiddlewareToGroup('web', CreateFreshApiToken::class);
        $router->pushMiddlewareToGroup('web', ViewShareUser::class);
    }

    /**
     * It register all views
     *
     * @return void
     */
    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../../../resources/views', 'gzero-core');
    }

    /**
     * It register all translations files
     *
     * @return void
     */
    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../../resources/lang', 'gzero-core');
    }

    /**
     * Add additional file to store routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/api.php');
    }

    /**
     * It registers all assets to publish
     *
     * @return void
     */
    protected function registerPublishes()
    {
        // Config
        $this->publishes(
            [
                __DIR__ . '/../../../config/config.php' => config_path('gzero.php'),
            ],
            'gzero-core config'
        );

        // Factories
        $this->publishes(
            [
                __DIR__ . '/../../../database/factories/UserFactory.php' => database_path('factories/gzero.php'),
            ],
            'gzero-core factories'
        );

        // Views
        $this->publishes(
            [
                __DIR__ . '/../../../resources/views' => resource_path('views/vendor/gzero-core'),
            ],
            'gzero-core views'
        );
    }

    /**
     * It registers global route patterns
     *
     * @return void
     */
    protected function registerRoutePatterns()
    {
        $router = resolve(Router::class);
        $router->pattern('id', '[0-9]+');
    }

}
