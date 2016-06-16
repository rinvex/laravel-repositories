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
     * @var string
     */
    protected $model;

    /**
     * The repository identifier.
     *
     * @var string
     */
    protected $repositoryId;

    /**
     * The repository cache status.
     *
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * The repository cache clear status.
     *
     * @var bool
     */
    protected $cacheClear = true;

    /**
     * Execute given callback and cache result set.
     *
     * @param string   $class
     * @param string   $method
     * @param string   $cacheKey
     * @param \Closure $closure
     *
     * @return mixed
     */
    protected function executeCallback($class, $method, $hash, Closure $closure)
    {
        $cacheKey = $class.'@'.$method.'.'.$hash;
        $config   = $this->getContainer('config')->get('rinvex.repository.cache');

        if ($this->cacheEnabled && $config['lifetime'] && in_array($method, $config['methods']) && ! $this->getContainer('request')->has($config['skip_uri'])) {
            if (method_exists($this->getContainer('cache')->getStore(), 'tags')) {
                return $config['lifetime'] === -1 ? $this->getContainer('cache')->tags($this->getRepositoryId())->rememberForever($cacheKey, $closure) : $this->getContainer('cache')->tags($this->getRepositoryId())->remember($cacheKey, $config['lifetime'], $closure);
            }

            // Store cache keys by mimicking cache tags
            $this->storeCacheKeys($class, $method, $hash, $config);

            return $config['lifetime'] === -1 ? $this->getContainer('cache')->rememberForever($cacheKey, $closure) : $this->getContainer('cache')->remember($cacheKey, $config['lifetime'], $closure);
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
     * Return the IoC container instance or any of it's services.
     *
     * @param String $service
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
     * Set the repository cache status.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function setCacheStatus($status)
    {
        $this->cacheEnabled = (bool) $status;

        return $this;
    }

    /**
     * Get the repository cache status.
     *
     * @return bool
     */
    public function getCacheStatus()
    {
        return $this->cacheEnabled;
    }

    /**
     * Set the repository cache clear status.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function setCacheClearStatus($status)
    {
        $this->cacheClear = (bool) $status;

        return $this;
    }

    /**
     * Get the repository cache clear status.
     *
     * @return bool
     */
    public function getCacheClearStatus()
    {
        return $this->cacheClear;
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
     * @param  mixed  $relations
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
     * @param  string  $column
     * @param  string  $direction
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
     * @param  \Illuminate\Database\Eloquent\Scope|\Closure|string $scope
     * @param  \Closure|null                                       $implementation
     *
     * @throws \InvalidArgumentException
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
        return call_user_func_array([new static, $method], $parameters);
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
     * @param $class
     * @param $method
     * @param $hash
     * @param $config
     *
     * @return void
     */
    protected function storeCacheKeys($class, $method, $hash, $config)
    {
        $cacheKeys = json_decode(file_get_contents(file_exists($config['keys_file']) ? $config['keys_file'] : file_put_contents($config['keys_file'], null)), true) ?: [];

        if (! isset($cacheKeys[$class]) || ! in_array($method.'.'.$hash, $cacheKeys[$class])) {
            $cacheKeys[$class][] = $method.'.'.$hash;
            file_put_contents($config['keys_file'], json_encode($cacheKeys));
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
        $cacheKeys = json_decode(file_get_contents(file_exists($config['keys_file']) ? $config['keys_file'] : file_put_contents($config['keys_file'], null)), true) ?: [];

        if (isset($cacheKeys[get_called_class()]) && is_array($cacheKeys[get_called_class()])) {
            foreach ($cacheKeys[get_called_class()] as $cacheKey) {
                $flushedKeys[] = $cacheKey;
            }

            unset($cacheKeys[get_called_class()]);
            file_put_contents($config['keys_file'], json_encode($cacheKeys));
        }

        return $flushedKeys;
    }
}
