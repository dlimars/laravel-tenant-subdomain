<?php

return [

	/**
	 * Current Tenant Name
	 * This config is setup in execution time
	 */
	'name' => '',

	/**
	 * Subdomain argument
	 * 'subdomain' => 'argument'
	 * produces {argument}.host.com
	 */
	'subdomain' => env('TENANT_SUBDOMAIN_ARGUMENT', '_account_'),

	/**
	 * Domain name
	 */
	'host' => env('APP_HOST', 'example.com'),

	/**
	 * Set TRUE to change default database config in middleware 
	 */
	'database_change'=> true,

	/**
	 * Path to database configuration files
	 * Only used if `database_change` is true
	 */
	'database_path' => config_path('tenants'),

	/**
	 * Database configuration prefix
	 * Only used if `database_change` is true
	 * allow closure 'database_prefix' => function($subdomain) { return md5($subdomain); }
	 */
	'database_prefix' => 'database_',

	/**
	 * Database configuration suffix
	 * Only used if `database_change` is true
	 * allow closure 'database_sufix' => function($subdomain) { return md5($subdomain); }
	 */
	'database_suffix' => '.php',

	/**
	 * Automatic subdomain route bind, like this:
	 * 
	 * Defined Route:
	 * Route::get('user/{id}', ['as' => 'me', 'domain' => '{subdomain}.my.app'] ....
	 * 
	 * just call route('me', '123')
	 * no more -> route('me', ['my-subdomain', '123'])
	 */
	'autobind' => true,

];