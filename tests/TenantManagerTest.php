<?php

use Dlimars\Tenant\TenantManager;

class TenanManagerTest extends PHPUnit_Framework_TestCase
{
	private $config = [
		'subdomain' 		=> '_account_',
		'host'				=> 'example.app',
		'database_change'	=> true,
		'database_path'		=> __DIR__ . '/stubs',
		'database_prefix'	=> 'testing_',
		'database_suffix'	=> '_stub.php'
	];

	public function testGetFullDomain()
	{
		$tenant = $this->newTenantManager($this->config);
		$argument = $tenant->getFullDomain();
		$this->assertEquals('{_account_}.example.app', $argument);
	}

	public function testGetDatabaseConfigFileName()
	{
		$config = $this->config;
		$config['database_path'] = 'foo';
		$tenant = $this->newTenantManager($config);
		$filename = $tenant->getDatabaseConfigFileName('test');
		$this->assertEquals('foo/testing_test_stub.php', $filename);
	}

	private function newTenantManager($config)
	{
		$configMock = $this->getConfigMock($config);
		return new TenantManager($configMock);
	}

	private function getConfigMock(array $config)
	{
		$configMock = $this->getMock('Illuminate\Config\Repository');

		$configMap = [];

		foreach ($config as $key => $value) {
			$configMap[] = ['tenant.' . $key, null, $value];
		}

		$configMock->expects($this->any())
				   ->method('get')
             	   ->will($this->returnValueMap($configMap));

        return $configMock;
	}
}