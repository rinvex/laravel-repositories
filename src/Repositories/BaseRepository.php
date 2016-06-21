<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Rinvex Repository Package.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Rinvex Repository Package
 * License: The MIT License (MIT)
 * Link:    https://rinvex.com
 */

namespace Rinvex\Repository\Repositories;

use Closure;
use Illuminate\Contracts\Container\Container;
use Rinvex\Repository\Contracts\RepositoryContract;

abstract class BaseRepository implements RepositoryContract
{
    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The repository model.
     *
     * @var object
     */
    protected $model;

    /**
     * The repository identifier.
     *
     * @var string
     */
    protected $repositoryId;

    /**
     * Indicate if the repository cache is enabled.
     *
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * Indicate if the repository cache clear is enabled.
     *
     * @var bool
     */
    protected $cacheClearEnabled = true;

    /**
     * Execute given callback and cache result set.
     *
     * @param string   $class
     * @param string   $method
     * @param string   $hash
     * @param \Closure $closure
     * @param int      $lifetime
     * @param string   $driver
     *
     * @return mixed
     */
    protected function executeCallback($class, $method, $hash, $lifetime = null, $driver = null, Closure $closure)
    {
        $cacheKey = $class.'@'.$method.'.'.$hash;
        $config   = $this->getContainer('config')->get('rinvex.repository.cache');
        $lifetime = $lifetime ?: $config['lifetime'];

        // Switch cache driver on runtime
        if ($driver) {
            $this->getContainer('cache')->setDefaultDriver($driver);
        }

        if ($this->isCacheableMethod($config, $method, $lifetime)) {
            if (method_exists($this->getContainer('cache')->getStore(), 'tags')) {
                return $lifetime === -1
                    ? $this->getContainer('cache')->tags($this->getRepositoryId())->rememberForever($cacheKey, $closure)
                    : $this->getContainer('cache')->tags($this->getRepositoryId())->remember($cacheKey, $lifetime, $closure);
            }

            // Store cache keys by mimicking cache tags
            $this->storeCacheKeys($class, $method, $hash, $config['keys_file']);

            return $lifetime === -1
                ? $this->getContainer('cache')->rememberForever($cacheKey, $closure)
                : $this->getContainer('cache')->remember($cacheKey, $lifetime, $closure);
        }

        return call_user_func($closure);
    }

    /**
     * Set the IoC container instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the IoC container instance or any of it's services.
     *
     * @param string|null $service
     *
     * @return mixed
     */
    public function getContainer($service = null)
    {
        return is_null($service) ? ($this->container ?: app()) : ($this->container[$service] ?: app($service));
    }

    /**
     * Set the repository identifier.
     *
     * @param string $repositoryId
     *
     * @return $this
     */
    public function setRepositoryId($repositoryId)
    {
        $this->repositoryId = $repositoryId;

        return $this;
    }

    /**
     * Get the repository identifier.
     *
     * @return string
     */
    public function getRepositoryId()
    {
        return $this->repositoryId ?: get_called_class();
    }

    /**
     * Enable repository cache.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function enableCache($status = true)
    {
        $this->cacheEnabled = $status;

        return $this;
    }

    /**
     * Determine if repository cache is enabled.
     *
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->cacheEnabled;
    }

    /**
     * Enable repository cache clear.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function enableCacheClear($status = true)
    {
        $this->cacheClearEnabled = $status;

        return $this;
    }

    /**
     * Determine if repository cache clear is enabled.
     *
     * @return bool
     */
    public function isCacheClearEnabled()
    {
        return $this->cacheClearEnabled;
    }

    /**
     * Forget the repository cache.
     *
     * @return $this
     */
    public function forgetCache()
    {
        $lifetime = $this->getContainer('config')->get('rinvex.repository.cache.lifetime');

        if ($this->cacheEnabled && $lifetime) {
            if (method_exists($this->getContainer('cache')->getStore(), 'tags')) {
                $this->getContainer('cache')->tags($this->getRepositoryId())->flush();
            } else {
                // Flush cache keys by mimicking cache tags
                foreach ($this->flushCacheKeys() as $cacheKey) {
                    $this->getContainer('cache')->forget($cacheKey);
                }
            }

            $this->getContainer('events')->fire($this->getRepositoryId().'.entity.cache.flushed', [$this]);
        }

        return $this;
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param mixed $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Add an "order by" clause to the repository.
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Register a new global scope.
     *
     * @param \Illuminate\Database\Eloquent\Scope|\Closure|string $scope
     * @param \Closure|null                                       $implementation
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function addGlobalScope($scope, Closure $implementation = null)
    {
        return $this->model->addGlobalScope($scope, $implementation);
    }

    /**
     * Remove all or passed registered global scopes.
     *
     * @param array|null $scopes
     *
     * @return $this
     */
    public function withoutGlobalScopes(array $scopes = null)
    {
        $this->model = $this->model->withoutGlobalScopes($scopes);

        return $this;
    }

    /**
     * Dynamically pass missing static methods to the model.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([new static(), $method], $parameters);
    }

    /**
     * Dynamically pass missing methods to the model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $model = $this->model;

        return call_user_func_array([$model, $method], $parameters);
    }

    /**
     * Store cache keys by mimicking cache tags.
     *
     * @param string $class
     * @param string $method
     * @param string $hash
     * @param string $file
     *
     * @return void
     */
    protected function storeCacheKeys($class, $method, $hash, $file)
    {
        $cacheKeys = $this->getCacheKeys($file);

        if (! isset($cacheKeys[$class]) || ! in_array($method.'.'.$hash, $cacheKeys[$class])) {
            $cacheKeys[$class][] = $method.'.'.$hash;
            file_put_contents($file, json_encode($cacheKeys));
        }
    }

    /**
     * Flush cache keys by mimicking cache tags.
     *
     * @return array
     */
    protected function flushCacheKeys()
    {
        $flushedKeys = [];

        $config    = $this->getContainer('config')->get('rinvex.repository.cache');
        $cacheKeys = $this->getCacheKeys($config['keys_file']);

        if (isset($cacheKeys[get_called_class()]) && is_array($cacheKeys[get_called_class()])) {
            foreach ($cacheKeys[get_called_class()] as $cacheKey) {
                $flushedKeys[] = $cacheKey;
            }

            unset($cacheKeys[get_called_class()]);
            file_put_contents($config['keys_file'], json_encode($cacheKeys));
        }

        return $flushedKeys;
    }

    /**
     * Get cache keys file.
     *
     * @param string $file
     *
     * @return array
     */
    protected function getCacheKeys($file)
    {
        return json_decode(file_get_contents(file_exists($file) ? $file : file_put_contents($file, null)), true) ?: [];
    }

    /**
     * Determine if repository method is cacheable.
     *
     * @param array  $config
     * @param string $method
     * @param int    $lifetime
     *
     * @return bool
     */
    protected function isCacheableMethod($config, $method, $lifetime)
    {
        return $this->cacheEnabled && $lifetime
               && in_array($method, $config['methods'])
               && ! $this->getContainer('request')->has($config['skip_uri']);
    }
}
