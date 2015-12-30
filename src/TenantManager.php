<?php

namespace Dlimars\Tenant;

use Illuminate\Config\Repository as ConfigRepository;

class TenantManager
{

    /**
     * Configuration class
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Create a new TenantManager
     * @param Illuminate\Config\Repository $config Configuration class
     */
    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    /**
     * Get the domain configuration
     *
     * @return string like 'domain.app'
     */
    public function getDomain()
    {
        return $this->config->get('tenant.host');
    }

    /**
     * Get the full domain with subdomain configuration
     *
     * @return string like '{account}.domain.app'
     */
    public function getFullDomain()
    {
        return "{" . $this->config->get("tenant.subdomain") . "}."
                    . $this->config->get("tenant.host");
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
        return $this->config->get('tenant.database_path') .'/'. $prefix . $subdomain . $sufix;
    }

    /**
     * Get the prefix of database configuration
     *
     * @param string $subdomain
     * @return string
     */
    public function getDatabaseConfigPrefix($subdomain)
    {
        return is_callable($this->config->get('tenant.database_prefix'))
                        ? call_user_func_array($this->config->get('tenant.database_prefix'),[$subdomain])
                        : $this->config->get('tenant.database_prefix');
    }

    /**
     * Get the sufix of database configuration
     *
     * @param string $subdomain
     * @return string
     */
    public function getDatabaseConfigSufix($subdomain)
    {
        return is_callable($this->config->get('tenant.database_suffix'))
                        ? call_user_func_array($this->config->get('tenant.database_suffix'),[$subdomain])
                        : $this->config->get('tenant.database_suffix');
    }
}