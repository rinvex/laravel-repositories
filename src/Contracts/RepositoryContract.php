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
     * @return mixed
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
     * Set the repository cache lifetime.
     *
     * @param string $cacheLifetime
     *
     * @return $this
     */
    public function setCacheLifetime($cacheLifetime);

    /**
     * Get the repository cache lifetime.
     *
     * @return string
     */
    public function getCacheLifetime();

    /**
     * Set the repository cache driver.
     *
     * @param string $cacheDriver
     *
     * @return $this
     */
    public function setCacheDriver($cacheDriver);

    /**
     * Get the repository cache driver.
     *
     * @return string
     */
    public function getCacheDriver();

    /**
     * Enable repository cache clear.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function enableCacheClear($status);

    /**
     * Determine if repository cache clear is enabled.
     *
     * @return bool
     */
    public function isCacheClearEnabled();

    /**
     * Create a new repository model instance.
     *
     * @throws \Rinvex\Repository\Exceptions\RepositoryException
     *
     * @return object
     */
    public function createModel();

    /**
     * Forget the repository cache.
     *
     * @return $this
     */
    public function forgetCache();

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
     * @param  string  $attribute
     * @param  string  $operator
     * @param  mixed   $value
     * @param  string  $boolean
     *
     * @return $this
     */
    public function where($attribute, $operator = null, $value = null, $boolean = 'and');

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
    public function whereIn($attribute, $values, $boolean = 'and', $not = false);

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  string  $attribute
     * @param  mixed   $values
     * @param  string  $boolean
     *
     * @return $this
     */
    public function whereNotIn($attribute, $values, $boolean = 'and');

    /**
     * Add an "order by" clause to the repository.
     *
     * @param string $attribute
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($attribute, $direction = 'asc');

    /**
     * Find an entity by its primary key.
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
     * @param string $attribute
     * @param array  $values
     * @param array  $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereIn($attribute, array $values, $attributes = ['*']);

    /**
     * Find all entities matching whereNotIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereNotIn($attribute, array $values, $attributes = ['*']);

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
