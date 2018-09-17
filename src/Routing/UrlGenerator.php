<?php

namespace Dlimars\Tenant\Routing;

use InvalidArgumentException;
use Illuminate\Routing\UrlGenerator as CoreUrlGenerator;
use Illuminate\Routing\RouteCollection;
use Illuminate\Http\Request;
use Dlimars\Tenant\TenantManager;

class UrlGenerator extends CoreUrlGenerator
{
    /**
     * @var TenantManager
     */
    protected $tenantManager;

    /**
     * Create a new URL Generator instance.
     *
     * @param  \Illuminate\Routing\RouteCollection $routes
     * @param  \Illuminate\Http\Request $request
     * @param TenantManager $tenantManager
     */
    public function __construct(RouteCollection $routes, Request $request, TenantManager $tenantManager)
    {
        parent::__construct($routes, $request);
        $this->tenantManager = $tenantManager;
    }

    /**
     * Get the URL to a named route.
     *
     * @param  string  $name
     * @param  mixed   $parameters
     * @param  bool  $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        if (! is_null($route = $this->routes->getByName($name))) {

            $actions = $route->getAction();
            if(config('tenant.autobind')
                && isset($actions['domain'])
                && $actions['domain'] == $this->tenantManager->getFullDomain()) {
                $parameters = $this->mergeSubDomainParameters($parameters);
            }

            return $this->toRoute($route, $parameters, $absolute);
        }

        throw new InvalidArgumentException("Route [{$name}] not defined.");
    }

    /**
     * Merge user parameters with subdomain parameter
     *
     * @param array|string $parameters
     * @return array array of parameters
     */
    protected function mergeSubDomainParameters($parameters = [])
    {
        if(!is_array($parameters)) {
            $parameters = [$parameters];
        }

        if(isset($parameters[config('tenant.subdomain')])){
            $subDomain = $parameters[config('tenant.subdomain')];
            return array_replace($parameters, [config('tenant.subdomain') => $subDomain]);
        }

        if ($subDomain = $this->getSubDomainParameter()) {
            $parameters = array_replace($parameters, [config('tenant.subdomain') => $subDomain]);
        }
        return $parameters;
    }

    /**
     * Get the subdomain parameter value
     *
     * @return string|null subdomain parameter value
     */
    private function getSubDomainParameter()
    {
        if(\Route::current() && ($param = \Route::input(config('tenant.subdomain'))) ) {
            return $param;
        }
        return $this->extractSubdomainFromUrl();
    }

    /**
     * Extract the subdomain from url
     * @return string subdomain parameter value
     */
    private function extractSubdomainFromUrl()
    {
        if (\Request::getHost() != $this->tenantManager->getDomain()) {
            return str_ireplace( $this->tenantManager->getDomain(), "", \Request::getHost() );
        }
        return false;
    }
}
