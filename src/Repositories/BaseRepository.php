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
     * The repository identifier.
     *
     * @var string
     */
    protected $repositoryId;

    /**
     * The repository model.
     *
     * @var string
     */
    protected $model;

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
     * The query where clauses.
     *
     * @var array
     */
    protected $where = [];

    /**
     * The query whereIn clauses.
     *
     * @var array
     */
    protected $whereIn = [];

    /**
     * The query whereNotIn clauses.
     *
     * @var array
     */
    protected $whereNotIn = [];

    /**
     * The "offset" value of the query.
     *
     * @var int
     */
    protected $offset;

    /**
     * The "limit" value of the query.
     *
     * @var int
     */
    protected $limit;

    /**
     * The column to order results by.
     *
     * @var array
     */
    protected $orderBy = [];

    /**
     * Execute given callback and cache the result.
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
        $skipUri = $this->getContainer('config')->get('rinvex.repository.cache.skip_uri');

        // Check if cache is enabled
        if ($this->getCacheLifetime() && ! $this->getContainer('request')->has($skipUri)) {
            $repositoryId = $this->getRepositoryId();
            $lifetime     = $this->getCacheLifetime();
            $hash         = $this->generateHash($args);
            $cacheKey     = $class.'@'.$method.'.'.$hash;

            // Switch cache driver on runtime
            if ($driver = $this->getCacheDriver()) {
                $this->getContainer('cache')->setDefaultDriver($driver);
            }

            // We need cache tags, check if default driver supports it
            if (method_exists($this->getContainer('cache')->getStore(), 'tags')) {
                $result = $lifetime === -1
                    ? $this->getContainer('cache')->tags($repositoryId)->rememberForever($cacheKey, $closure)
                    : $this->getContainer('cache')->tags($repositoryId)->remember($cacheKey, $lifetime, $closure);

                // We're done, let's clean up!
                $this->resetRepository();

                return $result;
            }

            // Default cache driver doesn't support tags, let's do it manually
            $this->storeCacheKeys($class, $method, $hash);

            $result = $lifetime === -1
                ? $this->getContainer('cache')->rememberForever($cacheKey, $closure)
                : $this->getContainer('cache')->remember($cacheKey, $lifetime, $closure);

            // We're done, let's clean up!
            $this->resetRepository();

            return $result;
        }

        // Cache disabled, just execute qurey & return result
        $result = call_user_func($closure);

        // We're done, let's clean up!
        $this->resetRepository();

        return $result;
    }

    /**
     * Generate unique query hash.
     *
     * @param $args
     *
     * @return string
     */
    protected function generateHash($args)
    {
        return md5(json_encode($args + [
                $this->getRepositoryId(),
                $this->getModel(),
                $this->getCacheDriver(),
                $this->getCacheLifetime(),
                $this->relations,
                $this->where,
                $this->whereIn,
                $this->whereNotIn,
                $this->offset,
                $this->limit,
                $this->orderBy,
            ]));
    }

    /**
     * Reset repository to it's defaults.
     *
     * @return $this
     */
    protected function resetRepository()
    {
        $this->cacheLifetime = null;
        $this->cacheDriver   = null;
        $this->relations     = [];
        $this->where         = [];
        $this->whereIn       = [];
        $this->whereNotIn    = [];
        $this->offset        = null;
        $this->limit         = null;
        $this->orderBy       = [];

        return $this;
    }

    /**
     * Prepare query.
     *
     * @param object $model
     *
     * @return object
     */
    protected function prepareQuery($model)
    {
        // Set the relationships that should be eager loaded
        if ($this->relations) {
            $model = $model->with($this->relations);
        }

        // Add a basic where clause to the query
        foreach ($this->where as $where) {
            list($attribute, $operator, $value, $boolean) = array_pad($where, 4, null);

            $model = $model->where($attribute, $operator, $value, $boolean);
        }

        // Add a "where in" clause to the query
        foreach ($this->whereIn as $whereIn) {
            list($attribute, $values, $boolean, $not) = array_pad($whereIn, 4, null);

            $model = $model->whereIn($attribute, $values, $boolean, $not);
        }

        // Add a "where not in" clause to the query
        foreach ($this->whereNotIn as $whereNotIn) {
            list($attribute, $values, $boolean) = array_pad($whereNotIn, 3, null);

            $model = $model->whereNotIn($attribute, $values, $boolean);
        }

        // Set the "offset" value of the query
        if ($this->offset > 0) {
            $model = $model->offset($this->offset);
        }

        // Set the "limit" value of the query
        if ($this->limit > 0) {
            $model = $model->limit($this->limit);
        }

        // Add an "order by" clause to the query.
        if ($this->orderBy) {
            list($attribute, $direction) = $this->orderBy;

            $model = $model->orderBy($attribute, $direction);
        }

        return $model;
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
     * @return object
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
     * @param float|int $cacheLifetime
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
     * @return float|int
     */
    public function getCacheLifetime()
    {
        // Return value even if it's zero "0" (which means cache is disabled)
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
        if ($this->getCacheLifetime()) {
            // Flush cache tags
            if (method_exists($this->getContainer('cache')->getStore(), 'tags')) {
                $this->getContainer('cache')->tags($this->getRepositoryId())->flush();
            } else {
                // Flush cache keys, then forget actual cache
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
     * Add a basic where clause to the query.
     *
     * @param  string  $attribute
     * @param  string  $operator
     * @param  mixed   $value
     * @param  string  $boolean
     *
     * @return $this
     */
    public function where($attribute, $operator = null, $value = null, $boolean = 'and')
    {
        // The last `$boolean` expression is intentional to fix list() & array_pad() results
        $this->where[] = [$attribute, $operator, $value, $boolean ?: 'and'];

        return $this;
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $attribute
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     *
     * @return $this
     */
    public function whereIn($attribute, $values, $boolean = 'and', $not = false)
    {
        // The last `$boolean` & `$not` expressions are intentional to fix list() & array_pad() results
        $this->whereIn[] = [$attribute, $values, $boolean ?: 'and', (bool) $not];

        return $this;
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $attribute
     * @param  mixed   $values
     * @param  string  $boolean
     *
     * @return $this
     */
    public function whereNotIn($attribute, $values, $boolean = 'and')
    {
        // The last `$boolean` expression is intentional to fix list() & array_pad() results
        $this->whereNotIn[] = [$attribute, $values, $boolean ?: 'and'];

        return $this;
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param int $offset
     *
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param string $attribute
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($attribute, $direction = 'asc')
    {
        $this->orderBy = [$attribute, $direction];

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
}
