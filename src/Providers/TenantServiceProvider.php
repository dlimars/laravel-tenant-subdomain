<?php

namespace Dlimars\Tenant\Providers;

use Dlimars\Tenant\TenantManager;
use Illuminate\Routing\RoutingServiceProvider;
use Dlimars\Tenant\Routing\UrlGenerator;
use Dlimars\Tenant\Routing\routes;

class TenantServiceProvider extends RoutingServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishConfig();
        $this->registerSingletons();
        $this->registerUrlGenerator();
    }

    /**
     * Publica arquivo de configuração para a pasta de configurações do usuário
     *
     * Publish config files to config folder 
     * 
     * @return void
     */
    protected function publishConfig()
    {
        $configPath = __DIR__ . '/../config/tenant.php';
        $this->publishes([
            $configPath => config_path('tenant.php')
        ], 'config');
    }

    /**
     * Register singletons to container
     *
     * @return void
     */
    protected function registerSingletons()
    {
        $this->app->singleton('tenant.manager', function(){
            return new TenantManager(
                app('config'), app('db'), app('router')
            );
        });
    }

    /**
     * Override the UrlGenerator
     *
     * @return void
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function($app) {
            $routes = $app['router']->getRoutes();
            $app->instance('routes', $routes);
            $url = new UrlGenerator(
                $routes, $app->rebinding('request', $this->requestRebinder()), $app->make('tenant.manager')
            );
            $url->setSessionResolver(function () {
                return $this->app['session'];
            });
            return $url;
        });
    }
}