<?php

namespace Dlimars\Tenant\Middlewares;

use Illuminate\Contracts\Routing\Middleware;
use Dlimars\Tenant\TenantManager;
use Illuminate\Routing\Router;
use Illuminate\Database\DatabaseManager;
use Closure;

class TenantDatabase
{

    /**
     * @var \Dlimars\Tenant\TenantManager
     */
    protected $tenantManager;

    /**
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;

    public function __construct(TenantManager $tenantManager, Router $router, DatabaseManager $db)
    {
        $this->tenantManager = $tenantManager;
        $this->router = $router;
        $this->db = $db;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = $this->router->getRoutes()->match($request);
        
        if($route) {
            $subdomain = $route->parameter( config('tenant.subdomain') );

            $config = $this->tenantManager->getDatabaseConfig($subdomain);

            if ($config) {
                config()->set("database.connections.tenant", $config);

                $this->db->setDefaultConnection('tenant');
                $this->db->reconnect('tenant');

                return $next($request);
            }
        }
        abort(404);
    }
}
