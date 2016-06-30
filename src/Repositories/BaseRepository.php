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
     * The relations to eager load on query execution.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * The repository cache lifetime.
     *
     * @var int
     */
    protected $cacheLifetime;

    /**
     * The repository cache driver.
     *
     * @var string
     */
    protected $cacheDriver;

    /**
     * Execute given callback and cache result set.
     *
     * @param string   $class
     * @param string   $method
     * @param array    $args
     * @param \Closure $closure
     *
     * @return mixed
     */
    protected function executeCallback($class, $method, $args, Closure $closure)
    {
        $driver   = $this->getCacheDriver();
        $lifetime = $this->getCacheLifetime();
        $hash     = md5(json_encode($args + [$driver, $lifetime, $this->model->toSql()]));
        $cacheKey = $class.'@'.$method.'.'.$hash;

        if ($driver) {
            // Switch cache driver on runtime
            $this->getContainer('cache')->setDefaultDriver($driver);
        }

        if ($this->isCacheable()) {
            if (method_exists($this->getContainer('cache')->getStore(), 'tags')) {
                return $lifetime === -1
                    ? $this->getContainer('cache')->tags($this->getRepositoryId())->rememberForever($cacheKey, $closure)
                    : $this->getContainer('cache')->tags($this->getRepositoryId())->remember($cacheKey, $lifetime, $closure);
            }

            // Store cache keys by mimicking cache tags
            $this->storeCacheKeys($class, $method, $hash);

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
     * Set the repository model.
     *
     * @param string $model
     *
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get the repository model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model ?: str_replace(['Repositories', 'Repository'], ['Models', ''], get_called_class());
    }

    /**
     * Set the repository cache lifetime.
     *
     * @param int $cacheLifetime
     *
     * @return $this
     */
    public function setCacheLifetime($cacheLifetime)
    {
        $this->cacheLifetime = $cacheLifetime;

        return $this;
    }

    /**
     * Get the repository cache lifetime.
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return ! is_null($this->cacheLifetime)
            ? $this->cacheLifetime
            : $this->getContainer('config')->get('rinvex.repository.cache.lifetime');
    }

    /**
     * Set the repository cache driver.
     *
     * @param string $cacheDriver
     *
     * @return $this
     */
    public function setCacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;

        return $this;
    }

    /**
     * Get the repository cache driver.
     *
     * @return string
     */
    public function getCacheDriver()
    {
        return $this->cacheDriver;
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
        if ($this->isCacheEnabled() && $this->getCacheLifetime()) {
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
     * @param array $relations
     *
     * @return $this
     */
    public function with(array $relations)
    {
        $this->relations = $relations;

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
     *
     * @return void
     */
    protected function storeCacheKeys($class, $method, $hash)
    {
        $keysFile  = $this->getContainer('config')->get('rinvex.repository.cache.keys_file');
        $cacheKeys = $this->getCacheKeys($keysFile);

        if (! isset($cacheKeys[$class]) || ! in_array($method.'.'.$hash, $cacheKeys[$class])) {
            $cacheKeys[$class][] = $method.'.'.$hash;
            file_put_contents($keysFile, json_encode($cacheKeys));
        }
    }

    /**
     * Flush cache keys by mimicking cache tags.
     *
     * @return array
     */
    protected function flushCacheKeys()
    {
        $flushedKeys  = [];
        $calledClasss = get_called_class();
        $config       = $this->getContainer('config')->get('rinvex.repository.cache');
        $cacheKeys    = $this->getCacheKeys($config['keys_file']);

        if (isset($cacheKeys[$calledClasss]) && is_array($cacheKeys[$calledClasss])) {
            foreach ($cacheKeys[$calledClasss] as $cacheKey) {
                $flushedKeys[] = $calledClasss.'@'.$cacheKey;
            }

            unset($cacheKeys[$calledClasss]);
            file_put_contents($config['keys_file'], json_encode($cacheKeys));
        }

        return $flushedKeys;
    }

    /**
     * Get cache keys.
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
     * Determine if repository is cacheable.
     *
     * @return bool
     */
    protected function isCacheable()
    {
        $skipUri = $this->getContainer('config')->get('rinvex.repository.cache.skip_uri');

        return $this->isCacheEnabled() && $this->getCacheLifetime()
               && ! $this->getContainer('request')->has($skipUri);
    }
}
