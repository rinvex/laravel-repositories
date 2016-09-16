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
use Rinvex\Repository\Traits\Cacheable;
use Illuminate\Contracts\Container\Container;
use Rinvex\Repository\Contracts\CacheableContract;
use Rinvex\Repository\Contracts\RepositoryContract;

abstract class BaseRepository implements RepositoryContract, CacheableContract
{
    use Cacheable;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The connection name for the repository.
     *
     * @var string
     */
    protected $connection;

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
     * The query whereHas clauses.
     *
     * @var array
     */
    protected $whereHas = [];

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
     * Execute given callback and return the result.
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
            return $this->cacheCallback($class, $method, $args, $closure);
        }

        // Cache disabled, just execute query & return result
        $result = call_user_func($closure);

        // We're done, let's clean up!
        $this->resetRepository();

        return $result;
    }

    /**
     * Reset repository to it's defaults.
     *
     * @return $this
     */
    protected function resetRepository()
    {
        $this->relations  = [];
        $this->where      = [];
        $this->whereIn    = [];
        $this->whereNotIn = [];
        $this->whereHas   = [];
        $this->offset     = null;
        $this->limit      = null;
        $this->orderBy    = [];

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
        if (! empty($this->relations)) {
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

        // Add a "where has" clause to the query
        foreach ($this->whereHas as $whereHas) {
            list($relation, $callback, $operator, $count) = array_pad($whereHas, 4, null);

            $model = $model->whereHas($relation, $callback, $operator, $count);
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
        if (! empty($this->orderBy)) {
            list($attribute, $direction) = $this->orderBy;

            $model = $model->orderBy($attribute, $direction);
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer($service = null)
    {
        return is_null($service) ? ($this->container ?: app()) : ($this->container[$service] ?: app($service));
    }

    /**
     * {@inheritdoc}
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function setRepositoryId($repositoryId)
    {
        $this->repositoryId = $repositoryId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryId()
    {
        return $this->repositoryId ?: get_called_class();
    }

    /**
     * {@inheritdoc}
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        $model = $this->getContainer('config')->get('rinvex.repository.models');

        return $this->model ?: str_replace(['Repositories', 'Repository'], [$model, ''], get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $this->relations = $relations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function where($attribute, $operator = null, $value = null, $boolean = 'and')
    {
        // The last `$boolean` expression is intentional to fix list() & array_pad() results
        $this->where[] = [$attribute, $operator, $value, $boolean ?: 'and'];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function whereIn($attribute, $values, $boolean = 'and', $not = false)
    {
        // The last `$boolean` & `$not` expressions are intentional to fix list() & array_pad() results
        $this->whereIn[] = [$attribute, $values, $boolean ?: 'and', (bool) $not];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function whereNotIn($attribute, $values, $boolean = 'and')
    {
        // The last `$boolean` expression is intentional to fix list() & array_pad() results
        $this->whereNotIn[] = [$attribute, $values, $boolean ?: 'and'];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function whereHas($relation, Closure $callback, $operator = '>=', $count = 1)
    {
        // The last `$operator` & `$count` expressions are intentional to fix list() & array_pad() results
        $this->whereHas[] = [$relation, $callback, $operator ?: '>=', $count ?: 1];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy($attribute, $direction = 'asc')
    {
        $this->orderBy = [$attribute, $direction];

        return $this;
    }

    /**
     * Create or update a new entity with the given attributes.
     *
     * @param array $attributes
     * @param mixed $id
     *
     * @return array
     */
    public function store($id, array $attributes = [])
    {
        return ! $id ? $this->create($attributes) : $this->update($id, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->getContainer('db')->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $this->getContainer('db')->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        $this->getContainer('db')->rollBack();
    }

    /**
     * {@inheritdoc}
     */
    public static function __callStatic($method, $parameters)
    {
        return call_user_func_array([new static(), $method], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->createModel(), $method], $parameters);
    }
}
