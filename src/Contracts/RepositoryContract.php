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
     * Return the IoC container instance or any of it's services.
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
     * Enable repository cache.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function enableCache($status = true);

    /**
     * Determine if repository cache is enabled.
     *
     * @return bool
     */
    public function isCacheEnabled();

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
     * Retrieve the repository model.
     *
     * @param mixed $model
     * @param array $data
     *
     * @throws \Rinvex\Repository\Exceptions\RepositoryException
     *
     * @return object
     */
    public function retrieveModel($model = null, array $data = []);

    /**
     * Forget the repository cache.
     *
     * @return $this
     */
    public function forgetCache();

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param mixed $relations
     *
     * @return $this
     */
    public function with($relations);

    /**
     * Add an "order by" clause to the repository.
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc');

    /**
     * Find an entity by its primary key.
     *
     * @param int   $id
     * @param array $columns
     * @param array $with
     *
     * @return object
     */
    public function find($id, $columns = ['*'], $with = []);

    /**
     * Find an entity by one of it's attributes.
     *
     * @param string $attribute
     * @param string $value
     * @param array  $columns
     * @param array  $with
     *
     * @return object
     */
    public function findBy($attribute, $value, $columns = ['*'], $with = []);

    /**
     * Find all entities.
     *
     * @param array $columns
     * @param array $with
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAll($columns = ['*'], $with = []);

    /**
     * Paginate all entities.
     *
     * @param int|null $perPage
     * @param array    $columns
     * @param string   $pageName
     * @param int|null $page
     *
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);

    /**
     * Find all entities matching where conditions.
     *
     * @param array $where
     * @param array $columns
     * @param array $with
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhere(array $where, $columns = ['*'], $with = []);

    /**
     * Find all entities matching whereIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $columns
     * @param array  $with
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereIn($attribute, array $values, $columns = ['*'], $with = []);

    /**
     * Find all entities matching whereNotIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $columns
     * @param array  $with
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereNotIn($attribute, array $values, $columns = ['*'], $with = []);

    /**
     * Create a new entity with the given attributes.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function create(array $attributes = []);

    /**
     * Find entity matching the given attributes or create it.
     *
     * @param array $attributes
     *
     * @return object|array
     */
    public function findOrCreate(array $attributes);

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
     * Register a new global scope.
     *
     * @param \Illuminate\Database\Eloquent\Scope|\Closure|string $scope
     * @param \Closure|null                                       $implementation
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function addGlobalScope($scope, Closure $implementation = null);

    /**
     * Remove all or passed registered global scopes.
     *
     * @param array|null $scopes
     *
     * @return $this
     */
    public function withoutGlobalScopes(array $scopes = null);

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
