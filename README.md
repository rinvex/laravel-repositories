# Rinvex Repository

**Rinvex Repository** is an intuitive, smart, and simple implementation of Repository Pattern used to abstract the data layer, making apps more flexible to maintain adhering to SOLID principles.

[![Packagist](https://img.shields.io/packagist/v/rinvex/repository.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rinvex/repository)
[![License](https://img.shields.io/packagist/l/rinvex/repository.svg?label=License&style=flat-square)](https://github.com/rinvex/repository/blob/develop/LICENSE)
[![VersionEye Dependencies](https://img.shields.io/versioneye/d/php/rinvex:repository.svg?label=Dependencies&style=flat-square)](https://www.versioneye.com/php/rinvex:repository/)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/rinvex/repository.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/rinvex/repository/)
[![Code Climate](https://img.shields.io/codeclimate/github/rinvex/repository.svg?label=CodeClimate&style=flat-square)](https://codeclimate.com/github/rinvex/repository)
[![StyleCI](https://styleci.io/repos/61269204/shield)](https://styleci.io/repos/61269204)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/8394bf3e-26c8-415a-8952-078b41110181.svg?label=SensioLabs&style=flat-square)](https://insight.sensiolabs.com/projects/8394bf3e-26c8-415a-8952-078b41110181)


## Table Of Contents

- [Installation](#installation)
    - [Compatibility](#compatibility)
    - [Prerequisites](#prerequisites)
    - [Require Package](#require-package)
    - [Install Dependencies](#install-dependencies)
- [Integration](#integration)
    - [Native Integration](#native-integration)
    - [Laravel Integration](#laravel-integration)
- [Config Options](#config-options)
- [Usage](#usage)
    - [EloquentRepository](#eloquentrepository)
        - [`setContainer()`, `getContainer()`](#setcontainer-getcontainer)
        - [`setRepositoryId()`, `getRepositoryId()`](#setrepositoryid-getrepositoryid)
        - [`enableCache()`, `isCacheEnabled()`](#enablecache-iscacheenabled)
        - [`enableCacheClear()`, `isCacheClearEnabled()`](#enablecacheclear-iscacheclearenabled)
        - [`addGlobalScope()`, `withoutGlobalScopes()`](#addglobalscope-withoutglobalscopes)
        - [`retrieveModel()`](#retrievemodel)
        - [`forgetCache()`](#forgetcache)
        - [`find()`](#find)
        - [`with()`](#with)
        - [`orderBy()`](#orderby)
        - [`findBy()`](#findby)
        - [`findAll()`](#findall)
        - [`paginate()`](#paginate)
        - [`findWhere()`](#findwhere)
        - [`findWhereIn()`](#findwherein)
        - [`findWhereNotIn()`](#findwherenotin)
        - [`create()`](#create)
        - [`findOrCreate()`](#findorcreate)
        - [`update()`](#update)
        - [`delete()`](#delete)
    - [EloquentRepository Fired Events](#eloquentrepository-fired-events)
    - [Mandatory Repository Conventions](#mandatory-repository-conventions)
    - [Automatic Guessing](#automatic-guessing)
    - [Flexible Caching](#flexible-caching)
- [Changelog](#changelog)
- [Support](#support)
- [Contributing & Protocols](#contributing--protocols)
- [Security Vulnerabilities](#security-vulnerabilities)
- [About Rinvex](#about-rinvex)
- [License](#license)


## Installation

The best and easiest way to install this package is through [Composer](https://getcomposer.org/).

### Compatibility

This package fully compatible with **Laravel** `5.2.*`.

> **Note:** Global scope features not tested with Laravel 5.1, and probably won't work as it has been drastically changed in 5.2 releases. Checkout Laravel's [Global Scopes](https://laravel.com/docs/5.2/eloquent#global-scopes) documentation for further details.

### Prerequisites

```json
"illuminate/events": "5.1.*|5.2.*",
"illuminate/support": "5.1.*|5.2.*",
"illuminate/database": "5.1.*|5.2.*",
"illuminate/container": "5.1.*|5.2.*",
"illuminate/contracts": "5.1.*|5.2.*"
```

### Require Package

Open your application's `composer.json` file and add the following line to the `require` array:
```json
"rinvex/repository": "1.0.*"
```

> **Note:** Make sure that after the required changes your `composer.json` file is valid by running `composer validate`.

### Install Dependencies

On your terminal run `composer install` or `composer update` command according to your application's status to install the new requirements.

> **Note:** Checkout Composer's [Basic Usage](https://getcomposer.org/doc/01-basic-usage.md) documentation for further details.


## Integration

**Rinvex Repository** package is framework-agnostic and as such can be integrated easily natively or with your favorite framework.

### Native Integration

Integrating the package outside of a framework is incredibly easy, just require the `vendor/autoload.php` file to autoload the package.

> **Note:** Checkout Composer's [Autoloading](https://getcomposer.org/doc/01-basic-usage.md#autoloading) documentation for further details.

### Laravel Integration

**Rinvex Repository** package supports Laravel by default and it comes bundled with a Service Provider for easy integration with the framework.

After installing the package, open your Laravel config file located at `config/app.php` and add the following service provider to the `$providers` array:
```php
Rinvex\Repository\RepositoryServiceProvider::class,
```

> **Note:** Checkout Laravel's [Service Providers](https://laravel.com/docs/5.2/providers) and [Service Container](https://laravel.com/docs/5.2/container) documentation for further details.

Run the following command on your terminal to publish config files:
```shell
php artisan vendor:publish --provider="Rinvex\Repository\RepositoryServiceProvider" --tag="config"
```

> **Note:** Checkout Laravel's [Configuration](https://laravel.com/docs/5.2/#configuration) documentation for further details.

You are good to go. Integration is done and you can now use all the available methods, proceed to the [Usage](#usage) section for an example.


## Config Options

If you followed the previous integration steps, then your published config file reside at `config/rinvex.themes.php`.

Config options are very expressive and self explanatory, as follows:
```php
return [

    /*
    |--------------------------------------------------------------------------
    | Caching Strategy
    |--------------------------------------------------------------------------
    */

    'cache' => [

        /*
        |--------------------------------------------------------------------------
        | Cache Keys File
        |--------------------------------------------------------------------------
        |
        | Here you may specify the cache keys file that is used only with cache
        | drivers that does not support cache tags. It is mandatory to keep
        | track of cache keys for later usage on cache flush process.
        |
        | Default: storage_path('framework/cache/rinvex.repository.json')
        |
        */

        'keys_file' => storage_path('framework/cache/rinvex.repository.json'),

        /*
        |--------------------------------------------------------------------------
        | Cache Lifetime
        |--------------------------------------------------------------------------
        |
        | Here you may specify the number of minutes that you wish the cache
        | to be remembered before it expires. If you want the cache to be
        | remembered forever, set this option to -1. 0 means disabled.
        |
        | Default: -1
        |
        */

        'lifetime' => -1,

        /*
        |--------------------------------------------------------------------------
        | Cache Clear
        |--------------------------------------------------------------------------
        |
        | Specify which actions would you like to clear cache upon success.
        | All repository cached data will be cleared accordingly.
        |
        | Default: ['create', 'update', 'delete']
        |
        */

        'clear_on' => [
            'create',
            'update',
            'delete',
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Skipping URI
        |--------------------------------------------------------------------------
        |
        | For testing purposes, or maybe some certain situations, you may wish
        | to skip caching layer and get fresh data result set just for the
        | current request. This option allows you to specify custom
        | URL parameter for skipping caching layer easily.
        |
        | Default: 'skipCache'
        |
        */

        'skip_uri' => 'skipCache',

        /*
        |--------------------------------------------------------------------------
        | Cache Methods
        |--------------------------------------------------------------------------
        |
        | Specify which methods should be cached. Note that these methods
        | MUST support caching in it's implementation for this to work.
        |
        | Default: ['find', 'findBy', 'findAll', 'paginate', 'findWhere', 'findWhereIn', 'findWhereNotIn']
        |
        */

        'methods' => [
            'find',
            'findBy',
            'findAll',
            'paginate',
            'findWhere',
            'findWhereIn',
            'findWhereNotIn',
        ],

    ],

];
```


## Usage

### EloquentRepository

The `Rinvex\Repository\Repositories\BaseRepository` is an abstract class with bare minimum implementation that concrete implementations must extend. 
The `Rinvex\Repository\Repositories\EloquentRepository` is currently the only available repository implementation, it makes it easy to create new instances of a model and to retrieve or override the model during runtime, in addition to performing multiple useful operations on models. To use `EloquentRepository` your repository MUST extend it first:
```php
use Rinvex\Repository\Repositories\EloquentRepository;

class FooRepository extends EloquentRepository
{
    // Inistantiate repository object with required data
    public function __construct(Application $app)
    {
        $this->setContainer($app)
             ->retrieveModel(\App\User::class)
             ->setRepositoryId('rinvex.repository');
    }
}

// Inistantiate repository
$repository = new \FooRepository();
```

As you can see, you have to inistantiate your repository object with required data, and the best place to do so is through the constructor method. Set the application container, retrieve model, set the repository ID, and you're good to go.
Through the `setContainer` method, it's easy to swap application container instances used within the repository.

#### `setContainer()`, `getContainer()`

The `setContainer` method sets the IoC container instance, while `getContainer` returns it:
```php
// Set the IoC container instance
$this->setContainer(new \Illuminate\Container\Container());

// Get the IoC container instance:
$container = $this->getContainer();
```

#### `setRepositoryId()`, `getRepositoryId()`

The `setRepositoryId` method sets the repository identifier, while `getRepositoryId` returns it:
```php
// Set repository identifier
$repository->setRepositoryId('rinvex.repository.entity');

// Get repository identifier
$repositoryId = $repository->getRepositoryId();
```

#### `enableCache()`, `isCacheEnabled()`

The `enableCache` method enables repository cache, while `isCacheEnabled` determines it's state:
```php
// Enable repository cache
$repository->enableCache(true);

// Determine if repository cache is enabled
$repository->isCacheEnabled();
```

#### `enableCacheClear()`, `isCacheClearEnabled()`

The `enableCacheClear` method enables repository cache clear, while `isCacheClearEnabled` determines it's state:
```php
// Enable repository cache clear
$repository->enableCacheClear(true);

// Determine if repository cache clear is enabled
$repository->isCacheClearEnabled();
```

#### `addGlobalScope()`, `withoutGlobalScopes()`

The `addGlobalScope` method registers a new global scope on the model while `withoutGlobalScopes` removes all or passed registered global scopes:
```php
// Register a new global scope on the model
$repository->addGlobalScope('age', function(Builder $builder) {
    $builder->where('age', '>', 200);
});

// Remove all or passed registered global scopes
$repository->withoutGlobalScopes(['age'])
```

> **Note:** Checkout Laravel's [Global Scopes](https://laravel.com/docs/5.2/eloquent#global-scopes) documentation for further details.

#### `retrieveModel()`

The `retrieveModel` method retrieves the repository model:
```php
$model = $repository->retrieveModel(\App\User::class);
```

#### `forgetCache()`

The `forgetCache()` method forgets the repository cache:
```php
$repository->forgetCache();
```

#### `with()`

The `with` method sets the relationships that should be eager loaded:
```php
$repository->with(['relationship']);
```

#### `orderBy()`

The `orderBy` method adds an "order by" clause to the repository:
```php
$repository->orderBy('id', 'asc');
```

#### `find()`

The `find` method finds an entity by its primary key:
```php
$entity = $repository->find(1);
```

#### `findBy()`

The `findBy` method finds an entity by one of it's attributes:
```php
$entity = $repository->findBy('id', 1);
```

#### `findAll()`

The `findAll` method finds all entities:
```php
$allEntities = $repository->findAll();
```

#### `paginate()`

The `paginate` method paginates all entities:
```php
$paginatedEntities = $repository->paginate(15);
```

#### `findWhere()`

The `findWhere` method finds all entities matching where conditions:
```php
$singleEntity = $repository->findWhere(['id' => 1]);
```

#### `findWhereIn()`

The `findWhereIn` method finds all entities matching whereIn conditions:
```php
$includedEntities = $repository->findWhereIn('id', [1, 2, 3]);
```

#### `findWhereNotIn()`

The `findWhereNotIn` method finds all entities matching whereNotIn conditions:
```php
$excludedEntities = $repository->findWhereNotIn('id', [1, 2, 3]);
```

#### `create()`

The `create` method creates a new entity with the given attributes:
```php
$createdEntity = $repository->create(['name' => 'Example']);

// Assign created entity status and instance variables:
list($status, $instance) = $createdEntity;
```

#### `findOrCreate()`

The `findOrCreate` method finds entity matching the given attributes or create it:
```php
$fetchedEntity = $repository->findOrCreate(['name' => 'Example']);
```

#### `update()`

The `update` method updates an entity with the given attributes:
```php
$updatedEntity = $repository->update(1, ['name' => 'Example2']);
```

#### `delete()`

The `delete` method deletes an entity with the given id:
```php
$deletedEntity = $repository->delete(1);
```

> **Notes:** 
> - All setter methods returns an instance of the current object, and thus can be chained.
> - All `find` method result sets are cached if cache is enabled and allowed for the method.
> - All `find` methods take two more optional parameters for selected columns and eager loading relations. By default all columns are selected.
> - All model methods can be called on repositories since it transparently passes it all through to the model even if not explicitly defined in the repository’s implementation.
> - Cache is enabled by default, but you can disable caching if you want per repository as shown above. Cache tags are maintained behind scenes even for cache drivers that doesn't support it.
> - `create`, `update`, and `delete` methods always return an array with two values, the first is action status whether it's succeeded or failed as a boolean value, and the other is an instance of the model just operated upon.
> - It's recommended to set IoC container instance, repository identifier, and model name explicitely through your repository constructor like the above example, but this package is smart enough to guess any required piece of data if it's missing.

### EloquentRepository Fired Events

Repositories fire events at every successful action, like `create`, `update`, `delete`. All fired events are prefixed with repository's identifier like the following example:

- rivnex.repository.entity.created
- rivnex.repository.entity.updated
- rivnex.repository.entity.deleted

For your convenience, the events suffixed with `.entity.created`, `.entity.updated`, or `.entity.deleted` have listeners that take actions accordingly. Usually we need to flush cache -if enabled/exists- upon every success action.
There's one more event `rivnex.repository.entity.cache.flushed` that's fired on cache flush. It has no listeners by default, but you may need to listen to it for relashionship actions.

### Mandatory Repository Conventions

Here some conventions important to know while using this package. This package adheres to best practices trying to make development easier for web artisan, and thus it has some conventions for standardization and interoperability.

- All Fired Events has a unique suffix, like `.entity.created` for example. Note the `.entity.` which is mandatory for automatic event listeners to subscribe to. 
- Default Directory Structure of any package integrates with this package is as follows:
```
├── config                  --> config files
|
├── database
|   ├── factories           --> database factory files
|   ├── migrations          --> database migration files
|   └── seeds               --> database seed files
|
├── resources
|   └── lang
|       └── en              --> English language files
|
├── src                     --> self explanatory directories
|   ├── Console
|   |   └── Commands
|   |
|   ├── Http
|   |   ├── Controllers
|   |   ├── Middleware
|   |   ├── Requests
|   |   └── routes.php
|   |
|   ├── Events
|   ├── Exceptions
|   ├── Facades
|   ├── Jobs
|   ├── Listeners
|   ├── Models
|   ├── Overrides
|   ├── Policies
|   ├── Providers
|   ├── Repositories
|   ├── Scopes
|   ├── Support
|   └── Traits
|
└── composer.json           --> composer dependencies file
```

> **Notes:**
> - This package adheres to [PSR-4: Autoloader](http://www.php-fig.org/psr/psr-4/) and expects all integrated packages to adhere to the same standard as well.
> - That full structure may not required by this package, or even other packages, but it's the standard for all Rinvex packages. It's also used for automatic resolving, such as when repository model is missing for example, it will be auto guessed and resolved according to this directory structure.

### Automatic Guessing

While it's recomended to explicitely set application container, repository model, and repository ID; This package is smart enough to guess any of these required data whenever missing.

- Application Container: `app()` helper is used as a fallback if application container instance not provided explicitely.
- Repository Model: Conventionally repositories namespaced like `Rinvex\Demos\Repositories\ItemRepository`, so corresponding model supposed to be namespaced like `Rinvex\Demos\Models\Item`. That's how this packages guess the model if it's missing.
- Repository Identifier: It's recommended to set repository identifier as a doted name like `rinvex.repository.entity`, but if it's missing fully qualified class name will be used (actually the result of `get_called_class()` function).

### Flexible Caching

**Rinvex Repository** has an powerful, yet simple caching system, that handles almost every edge case. While you can enable/disable your application's cache as a whole, you have the ability to enable/disable cache individually per repository through the following attribute:
```php
$repository->enableCache(true);
```

Additionally, you have the flexibility to control cache even more granualy and enable/disable cache per method. Checkout the `rinvex.repository.cache.methods` config option for a list of cached methods.

Lastly, you can disable cache per single request by passing the following query string in your URL `skipCache`. Note that you can modify this parameter to whatever you need through the `rinvex.repository.cache.skip_uri`.

> **Notes:** 
> - You can control how long repository cache lasts through the `rinvex.repository.cache.lifetime` config option.
> - This package utilizes cache tags in a very smart way, even if your chosen cache driver doesn't support cache tags it will manage virtually on it's own for precise cache management. Behind scenes it uses a json file to store cache keys that you can modify through the `rinvex.repository.cache.keys_file` config option.


## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.


## Support

The following support channels are available at your fingertips:

- [Chat](https://slack.rinvex.com)
- [Email](mailto:help@rinvex.com)
- [Twitter](https://twitter.com/rinvex)


## Contributing & Protocols

Thank you for considering contributing to this project! The contribution guide can be found in [CONTRIBUTING.md](CONTRIBUTING.md).

Bug reports, feature requests, and pull requests are very welcome.

- [Versioning](CONTRIBUTING.md#versioning)
- [Support Policy](CONTRIBUTING.md#support-policy)
- [Coding Standards](CONTRIBUTING.md#coding-standards)
- [Pull Requests](CONTRIBUTING.md#pull-requests)


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to help@rinvex.com. All security vulnerabilities will be promptly addressed.


## About Rinvex

Rinvex is a software solutions startup, specialized in integrated enterprise solutions for SMEs established in Alexandria, Egypt since June 2016. We believe that our drive The Value, The Reach, and The Impact is what differentiates us and unleash the endless possibilities of our philosophy through the power of software. We like to call it Innovation At The Speed Of Life. That’s how we do our share of advancing humanity.


## License

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2016 Rinvex LLC, Some rights reserved.
