<?php

namespace Dlimars\Tenant\Providers\TenantServiceProvider;

use Dlimars\Tenant\Routing\UrlGenerator;

class TenantServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Override the url service container so we can do some magic with the action() helper.
        $this->app['url'] = $this->app->share(function($app) {
            $routes = $app['router']->getRoutes();
            $app->instance('routes', $routes);
            $url = new UrlGenerator(
                $routes, $app->rebinding('request', $this->requestRebinder())
            );
            $url->setSessionResolver(function () {
                return $this->app['session'];
            });
            return $url;
        });
    }
}