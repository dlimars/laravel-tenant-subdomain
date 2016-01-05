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
     * Make the configuration database config file
     * @param $subdomain Subdomain name
     * @param $config array database configuration
     * @return boolean file creation success
     */
    public function makeDatabaseConfigFile($subdomain, array $config)
    {
        $filename = $this->getDatabaseConfigFileName($subdomain);
        $content = "return " . $this->getArrayAsString($config) . ";";
        return (bool) file_put_contents($filename, $content);
    }

    /**
     * Drop the configuration database config file
     * @param $subdomain string subdomain name
     * @return boolean
     */
    public function dropDatabaseConfigFile($subdomain)
    {
        $filename = $this->getDatabaseConfigFileName($subdomain);
        if (file_exists($filename)) {
            return (bool) unlink($filename);
        }
        return false;
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

    /**
     * Tranform array in string
     * @param $data input array
     * @return string
     */
    private function getArrayAsString(array $data)
    {
        $output = "[\n\r";
        array_walk_recursive($data, function($value, $key) use (&$output) {
            $output.= "\t'$key' => '$value',\n\r";
        });
        return $output . "]";
    }
}