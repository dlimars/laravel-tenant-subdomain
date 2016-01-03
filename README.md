# Laravel Tenant Subdomínio
[![Build Status](https://travis-ci.org/dlimars/laravel-tenant-subdomain.svg)](https://travis-ci.org/dlimars/laravel-tenant-subdomain)
[![Latest Stable Version](https://poser.pugx.org/dlimars/laravel-tenant-subdomain/v/stable)](https://packagist.org/packages/dlimars/laravel-tenant-subdomain)
[![Total Downloads](https://poser.pugx.org/dlimars/laravel-tenant-subdomain/downloads)](https://packagist.org/packages/dlimars/laravel-tenant-subdomain)
[![Latest Unstable Version](https://poser.pugx.org/dlimars/laravel-tenant-subdomain/v/unstable)](https://packagist.org/packages/dlimars/laravel-tenant-subdomain)
[![License](https://poser.pugx.org/dlimars/laravel-tenant-subdomain/license)](https://packagist.org/packages/dlimars/laravel-tenant-subdomain)

Este pacote irá auxiliar na organização de clientes em subdomínios usando Laravel.

## Instalação
Adicione no seu `composer.json`

```js
"require": {
    //..
    "dlimars/laravel-tenant-subdomain": "dev-master"
},
```

ou execute em seu terminal
```
    composer require dlimars/laravel-tenant-subdomain
```

adicione o provider e o facade em `config/app.php`:

```php
'providers' => [
    // outros providers
    Dlimars\Tenant\Providers\TenantServiceProvider::class,
],

'aliases' => [
    // outros aliases
    'Tenant' => Dlimars\Tenant\Facades\Tenant::class,
]
```

adicione o middleware em `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    // outros middlewares
    'tenant.database' => \Dlimars\Tenant\Middlewares\TenantDatabase::class
];
```

Após isso, abra seu console e execute: `php artisan vendor:publish`, modifique o arquivo `config/tenant.php` para sua necessidade, abra seu arquivo `.env` e adicione:

```
APP_HOST=domain.com
TENANT_SUBDOMAIN_ARGUMENT=_account_
```

## Uso

para gerar rotas de subdominio, utilize da seguinte forma:

```php
// Tenant::getFullDomain() retorna algo como '{_account_}.domain.com'

Route::group(['domain' => Tenant::getFullDomain()], function () {
    Route::get('subdomain-teste/{id}', ['as' => 'subdomain-teste', function($subdomain, $id){
        return route('subdomain-teste', ['123']);
    }]);
});
```

para gerar rotas para a aplicação principal (que não seja subdominio), utilize da seguinte forma

```php
// Tenant::getDomain() retorna algo como 'domain.com'

Route::group(['domain' => Tenant::getDomain()], function () {
    Route::get('domain-teste/{id}', ['as' => 'domain-teste', function($id){
        return route('domain-teste', ['123']);
    }]);
});

// isso impede que rotas do dominio possam ser acessadas através do subdominio
```

## Carregando as configurações de banco de acordo com o subdominio

os arquivos de configurações de banco serão lidos por padrão, dentro da pasta `config/tenant`, com o exemplo de conteudo:

```php
return [
    'driver'    => 'mysql',
    'host'      => 'host',
    'database'  => 'db_subdomain',
    'username'  => 'user_subdomain',
    'password'  => 'user_password',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'strict'    => false,
];
```

o arquivo é lido e adicionado como conexão padrão `tenant`, isso é feito via Middleware, em todas as rotas que irão utilizar base de dados própria, use o middleware `tenant.database`:

```php
Route::group(['domain' => Tenant::getFullDomain(), 'middleware' => ['tenant.database']], function () {
    Route::get('domain-teste/{id}', ['as' => 'domain-teste', function($id){
        return route('domain-teste', ['123']);
    }]);
});
```

Supondo que o usuário acesse `http://beltrano.domain.com`, a configuração a ser carregada deverá estar em `/config/tenants/beltrano.php` (isso é configurável)
