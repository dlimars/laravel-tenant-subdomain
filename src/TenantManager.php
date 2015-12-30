<?php

namespace Dlimars\Tenant;

class TenantManager
{

    /**
     * Get the domain configuration
     *
     * @return string like 'domain.app'
     */
    public function getDomain()
    {
        return config('tenant.host');
    }

    /**
     * Get the full domain with subdomain configuration
     *
     * @return string like '{account}.domain.app'
     */
    public function getFullDomain()
    {
        return "{" . config("tenant.subdomain") . "}." . config("tenant.host");
    }

    /**
     * Check if current route is a subdomain
     *
     * @return bool
     */
    public function currentRouteIsSubdomain()
    {
        return
            count( explode('.', \Request::server('HTTP_HOST')) ) >=
            count( explode('.', $this->getFullDomain()) );
    }

    /**
     * Get the database config
     * 
     * @param $subdomain
     * @return array|false database configuration
     */
    public function getDatabaseConfig($subdomain)
    {
        $file = realpath($this->getDatabaseConfigFileName($subdomain));
        return $file ? require $file : false;
    }

    /**
     * Get the full database config file name
     *
     * @param string subdomain
     * @return string filename
     */
    public function getDatabaseConfigFileName($subdomain)
    {
        $prefix = $this->getDatabaseConfigPrefix($subdomain);
        $sufix  = $this->getDatabaseConfigSufix($subdomain);
        return config('tenant.database_path') .'/'. $prefix . $subdomain . $sufix;
    }

    /**
     * Get the prefix of database configuration
     *
     * @param string $subdomain
     * @return string
     */
    public function getDatabaseConfigPrefix($subdomain)
    {
        return is_callable(config('tenant.database_prefix'))
                        ? call_user_func_array(config('tenant.database_prefix'),[$subdomain])
                        : config('tenant.database_prefix');
    }

    /**
     * Get the sufix of database configuration
     *
     * @param string $subdomain
     * @return string
     */
    public function getDatabaseConfigSufix($subdomain)
    {
        return is_callable(config('tenant.database_suffix'))
                        ? call_user_func_array(config('tenant.database_suffix'),[$subdomain])
                        : config('tenant.database_suffix');
    }
}