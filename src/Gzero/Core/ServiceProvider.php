<?php namespace Gzero\Core;

use Carbon\Carbon;
use Gzero\Core\Http\Middleware\Timezone;
use Gzero\Core\Http\Middleware\ViewComposer;
use Gzero\Core\Policies\FilePolicy;
use Gzero\Core\Http\Middleware\Init;
use Gzero\Core\Http\Middleware\MultiLanguage;
use Gzero\Core\Http\Middleware\ViewShareUser;
use Gzero\Core\Models\File;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Option;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\User;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\Services\OptionService;
use Gzero\Core\Policies\OptionPolicy;
use Gzero\Core\Policies\UserPolicy;
use Gzero\Core\Policies\RoutePolicy;
use Gzero\Core\Services\RoutesService;
use Gzero\Core\Services\TimezoneService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Routing\Router;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;
use Laravel\Passport\Passport;
use Robbo\Presenter\PresenterServiceProvider;
use DaveJamesMiller\Breadcrumbs\Facade as BreadcrumbsFacade;
use DaveJamesMiller\Breadcrumbs\ServiceProvider as BreadcrumbServiceProvider;

class ServiceProvider extends AbstractServiceProvider {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [
        PresenterServiceProvider::class,
        BreadcrumbServiceProvider::class,
    ];

    /**
     * List of service providers aliases
     *
     * @var array
     */
    protected $aliases = [
        'options'   => OptionService::class,
        'timezones' => TimezoneService::class,
        'Breadcrumbs' => BreadcrumbsFacade::class
    ];

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Route::class  => RoutePolicy::class,
        User::class   => UserPolicy::class,
        Option::class => OptionPolicy::class,
        File::class   => FilePolicy::class
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

        if (config('app.env') === 'testing') {
            // We're registering all routes here to unify the whole process between Laravel & Orchestra setup
            $this->app->booted(function ($app) {
                $app->make(RoutesService::class)->registerAll();
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

        if (class_exists('Breadcrumbs')) {
            require __DIR__ . '/../../../routes/breadcrumbs.php';
        }
    }

    /**
     * Bind services
     *
     * @return void
     */
    protected function bindRepositories()
    {
        $this->app->singleton(RoutesService::class, function () {
            return new RoutesService();
        });

        $this->app->singleton(LanguageService::class, function () {
            return new LanguageService(
                cache()->rememberForever('languages', function () {
                    return Language::all();
                })
            );
        });

        $this->app->singleton('croppa.src_dir', function () {
            return resolve('filesystem')->disk(config('gzero.upload.disk'))->getDriver();
        });
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
        $this->mergeConfigFrom(__DIR__ . '/../../../config/config.php', 'gzero');
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
        resolve(Kernel::class)->prependMiddleware(MultiLanguage::class);
        resolve(Kernel::class)->prependMiddleware(Timezone::class);
        /** @var Router $router */
        $router = resolve(Router::class);
        $router->pushMiddlewareToGroup('web', CreateFreshApiToken::class);
        $router->pushMiddlewareToGroup('web', ViewComposer::class);
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
