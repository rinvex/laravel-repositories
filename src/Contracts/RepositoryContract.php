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
     * Set the relationships that should be eager loaded.
     *
     * @param array $relations
     *
     * @return $this
     */
    public function with(array $relations);

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
     * Create a new entity with the given attributes.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function create(array $attributes = []);

    /**
     * Update an entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return array
     */
    public function update($id, array $attributes = []);

    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @return array
     */
    public function delete($id);

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
