<?php

namespace Dlimars\Tenant;

use Illuminate\Config\Repository as ConfigRepository;

class TenantManager
{

    /**
     * Configuration class
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Create a new TenantManager
     * @param \Illuminate\Config\Repository $config Configuration class
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
     * Get the full domain with subDomain configuration
     *
     * @return string like '{account}.domain.app'
     */
    public function getFullDomain()
    {
        return "{" . $this->config->get("tenant.subDomain") . "}."
                    . $this->config->get("tenant.host");
    }

    /**
     * Get the database config
     * 
     * @param $subDomain
     * @return array|false database configuration
     */
    public function getDatabaseConfig($subDomain)
    {
        $file = realpath($this->getDatabaseConfigFileName($subDomain));
        return $file ? require $file : false;
    }

    /**
     * Make the configuration database config file
     * @param $subDomain String name
     * @param $config array database configuration
     * @return boolean file creation success
     */
    public function makeDatabaseConfigFile($subDomain, array $config)
    {
        $filename = $this->getDatabaseConfigFileName($subDomain);
        $content = "<?php\n\r\n\r" .
                    "return " . $this->getArrayAsString($config) . ";";
        return (bool) file_put_contents($filename, $content);
    }

    /**
     * Drop the configuration database config file
     * @param $subDomain string subDomain name
     * @return boolean
     */
    public function dropDatabaseConfigFile($subDomain)
    {
        $filename = $this->getDatabaseConfigFileName($subDomain);
        if (file_exists($filename)) {
            return (bool) unlink($filename);
        }
        return false;
    }

    /**
     * Get the full database config file name
     *
     * @param $subDomain string subDomain name
     * @return string filename
     */
    public function getDatabaseConfigFileName($subDomain)
    {
        $prefix = $this->getDatabaseConfigPrefix($subDomain);
        $suffix = $this->getDatabaseConfigSuffix($subDomain);
        return $this->config->get('tenant.database_path') .'/'. $prefix . $subDomain . $suffix . '.php';
    }

    /**
     * Get the prefix of database configuration
     *
     * @param string $subDomain
     * @return string
     */
    public function getDatabaseConfigPrefix($subDomain)
    {
        return is_callable($this->config->get('tenant.database_prefix'))
                        ? call_user_func_array($this->config->get('tenant.database_prefix'),[$subDomain])
                        : $this->config->get('tenant.database_prefix');
    }

    /**
     * Get the sufix of database configuration
     *
     * @param string $subDomain
     * @return string
     */
    public function getDatabaseConfigSuffix($subDomain)
    {
        return is_callable($this->config->get('tenant.database_suffix'))
                        ? call_user_func_array($this->config->get('tenant.database_suffix'),[$subDomain])
                        : $this->config->get('tenant.database_suffix');
    }

    /**
     * Transform array in string
     * @param $data array
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