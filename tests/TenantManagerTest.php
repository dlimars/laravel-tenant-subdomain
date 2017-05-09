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
		'database_suffix'	=> '_stub'
	];

	public function tearDown()
	{
		$this->resetTempFolder();
	}

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

	public function testGetDatabaseConfigIfFileExists()
	{
		$config = $this->config;
		$config['database_prefix'] = 'configuration_';
		$config['database_path'] = realpath(__DIR__ . '/stubs');
		$tenant = $this->newTenantManager($config);
		$configuration = $tenant->getDatabaseConfig('test');
		$this->assertInternalType('array', $configuration);
	}

	public function testMakeDatabaseConfigFile()
	{
		$config = $this->config;
		$config['database_path'] = realpath(__DIR__ . '/temp');
		$config['database_prefix'] = 'bar_';
		$config['database_suffix'] = '_baz';
		$tenant = $this->newTenantManager($config);
		$response = $tenant->makeDatabaseConfigFile('foo', ['bar' => 'baz'] );
		$this->assertTrue( $response );
		$this->assertFileExists( __DIR__ . '/temp/bar_foo_baz.php' );
		$fileCompare =  "<?php\n\r\n\r"
						."return [\n\r"
						. "\t'bar' => 'baz',\n\r"
						. "];";
		$fileContents = file_get_contents(__DIR__ . '/temp/bar_foo_baz.php');
		$this->assertEquals($fileCompare, $fileContents);
	}

	public function testDropDatabaseConfigFile()
	{
		$config = $this->config;
		$config['database_path'] = realpath(__DIR__ . '/temp');
		$config['database_prefix'] = 'foo_';
		$config['database_suffix'] = '_baz';
		$tenant = $this->newTenantManager($config);
		file_put_contents($config['database_path'] . "/foo_bar_baz.php", "foo_bar");
		$this->assertTrue($tenant->dropDatabaseConfigFile('bar'));
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

	private function resetTempFolder()
	{
		$files = glob(__DIR__ . '/temp/*.php');
		foreach ($files as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}
	}
}
