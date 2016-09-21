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

namespace Rinvex\Repository\Contracts;

use Closure;
use Illuminate\Contracts\Container\Container;

interface RepositoryContract
{
    /**
     * Set the IoC container instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container);

    /**
     * Get the IoC container instance or any of it's services.
     *
     * @param string|null $service
     *
     * @return object
     */
    public function getContainer($service = null);

    /**
     * Set the connection associated with the repository.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setConnection($name);

    /**
     * Get the current connection for the repository.
     *
     * @return string
     */
    public function getConnection();

    /**
     * Set the repository identifier.
     *
     * @param string $repositoryId
     *
     * @return $this
     */
    public function setRepositoryId($repositoryId);

    /**
     * Get the repository identifier.
     *
     * @return string
     */
    public function getRepositoryId();

    /**
     * Set the repository model.
     *
     * @param string $model
     *
     * @return $this
     */
    public function setModel($model);

    /**
     * Get the repository model.
     *
     * @return string
     */
    public function getModel();

    /**
     * Create a new repository model instance.
     *
     * @throws \Rinvex\Repository\Exceptions\RepositoryException
     *
     * @return object
     */
    public function createModel();

    /**
     * Get model instance of the given ID or a new empty one.
     *
     * @throws \Rinvex\Repository\Exceptions\RepositoryException
     *
     * @return object|null
     */
    public function getModelInstance($id);

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations);

    /**
     * Add a basic where clause to the query.
     *
     * @param string $attribute
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @return $this
     */
    public function where($attribute, $operator = null, $value = null, $boolean = 'and');

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $attribute
     * @param mixed  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return $this
     */
    public function whereIn($attribute, $values, $boolean = 'and', $not = false);

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $attribute
     * @param mixed  $values
     * @param string $boolean
     *
     * @return $this
     */
    public function whereNotIn($attribute, $values, $boolean = 'and');

    /**
     * Add a "where has relationship" clause to the query.
     *
     * @param string   $relation
     * @param \Closure $callback
     * @param string   $operator
     * @param int      $count
     *
     * @return $this
     */
    public function whereHas($relation, Closure $callback, $operator = '>=', $count = 1);

    /**
     * Set the "offset" value of the query.
     *
     * @param int $offset
     *
     * @return $this
     */
    public function offset($offset);

    /**
     * Set the "limit" value of the query.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit($limit);

    /**
     * Add an "order by" clause to the query.
     *
     * @param string $attribute
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($attribute, $direction = 'asc');

    /**
     * Find an entity by it's primary key.
     *
     * @param int   $id
     * @param array $attributes
     *
     * @return object
     */
    public function find($id, $attributes = ['*']);

    /**
     * Find an entity by one of it's attributes.
     *
     * @param string $attribute
     * @param string $value
     * @param array  $attributes
     *
     * @return object
     */
    public function findBy($attribute, $value, $attributes = ['*']);

    /**
     * Find the first entity.
     *
     * @param array $attributes
     *
     * @return object
     */
    public function findFirst($attributes = ['*']);

    /**
     * Find all entities.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAll($attributes = ['*']);

    /**
     * Paginate all entities.
     *
     * @param int|null $perPage
     * @param array    $attributes
     * @param string   $pageName
     * @param int|null $page
     *
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $attributes = ['*'], $pageName = 'page', $page = null);

    /**
     * Paginate all entities into a simple paginator.
     *
     * @param int|null $perPage
     * @param array    $attributes
     * @param string   $pageName
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $attributes = ['*'], $pageName = 'page');

    /**
     * Find all entities matching where conditions.
     *
     * @param array $where
     * @param array $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhere(array $where, $attributes = ['*']);

    /**
     * Find all entities matching whereIn conditions.
     *
     * @param array $where
     * @param array $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereIn(array $where, $attributes = ['*']);

    /**
     * Find all entities matching whereNotIn conditions.
     *
     * @param array $where
     * @param array $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereNotIn(array $where, $attributes = ['*']);

    /**
     * Find all entities matching whereHas conditions.
     *
     * @param array $where
     * @param array $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereHas(array $where, $attributes = ['*']);

    /**
     * Create a new entity with the given attributes.
     *
     * @param array $attributes
     *
     * @return object|bool
     */
    public function create(array $attributes = []);

    /**
     * Update an entity with the given attributes.
     *
     * @param int|object $id
     * @param array      $attributes
     *
     * @return object|bool
     */
    public function update($id, array $attributes = []);

    /**
     * Store the entity with the given attributes.
     *
     * @param int   $id
     * @param array $attributes
     *
     * @return object|bool
     */
    public function store($id, array $attributes = []);

    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @return object|bool
     */
    public function delete($id);

    /**
     * Start a new database transaction.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function beginTransaction();

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit();

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack();

    /**
     * Retrieve the "count" result of the query.
     *
     * @param string $columns
     * @return int
     */
    public function count($columns = '*');

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public function min($column);

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public function max($column);

    /**
     * Retrieve the average value of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public function avg($column);

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public function sum($column);

    /**
     * Dynamically pass missing static methods to the model.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters);

    /**
     * Dynamically pass missing methods to the model.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters);
}
