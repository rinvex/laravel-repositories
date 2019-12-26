<?php

declare(strict_types=1);

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
     * @return static
     */
    public function setContainer(Container $container);

    /**
     * Get the IoC container instance or any of its services.
     *
     * @param string|null $service
     *
     * @return mixed
     */
    public function getContainer($service = null);

    /**
     * Set the connection associated with the repository.
     *
     * @param string $name
     *
     * @return static
     */
    public function setConnection($name);

    /**
     * Get the current connection for the repository.
     *
     * @return string
     */
    public function getConnection(): string;

    /**
     * Set the repository identifier.
     *
     * @param string $repositoryId
     *
     * @return static
     */
    public function setRepositoryId($repositoryId);

    /**
     * Get the repository identifier.
     *
     * @return string
     */
    public function getRepositoryId(): string;

    /**
     * Set the repository model.
     *
     * @param string $model
     *
     * @return static
     */
    public function setModel($model);

    /**
     * Get the repository model.
     *
     * @return string
     */
    public function getModel(): string;

    /**
     * Create a new repository model instance.
     *
     * @throws \Rinvex\Repository\Exceptions\RepositoryException
     *
     * @return mixed
     */
    public function createModel();

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param array|string $relations
     *
     * @return static
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
     * @return static
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
     * @return static
     */
    public function whereIn($attribute, $values, $boolean = 'and', $not = false);

    /**
     * Add a "where not in" clause to the query.
     *
     * @param string $attribute
     * @param mixed  $values
     * @param string $boolean
     *
     * @return static
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
     * @return static
     */
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1);

    /**
     * Add a scope to the query.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return static
     */
    public function scope($name, array $parameters = []);

    /**
     * Set the "offset" value of the query.
     *
     * @param int $offset
     *
     * @return static
     */
    public function offset($offset);

    /**
     * Set the "limit" value of the query.
     *
     * @param int $limit
     *
     * @return static
     */
    public function limit($limit);

    /**
     * Add an "order by" clause to the query.
     *
     * @param string $attribute
     * @param string $direction
     *
     * @return static
     */
    public function orderBy($attribute, $direction = 'asc');

    /**
     * Add a "group by" clause to the query.
     *
     * @param array|string $column
     *
     * @return static
     */
    public function groupBy($column);

    /**
     * Add a "having" clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param string $boolean
     *
     * @return static
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and');

    /**
     * Add a "or having" clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     *
     * @return static
     */
    public function orHaving($column, $operator = null, $value = null);

    /**
     * Find an entity by its primary key.
     *
     * @param int   $id
     * @param array $attributes
     *
     * @return mixed
     */
    public function find($id, $attributes = ['*']);

    /**
     * Find an entity by its primary key or throw an exception.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function findOrFail($id, $attributes = ['*']);

    /**
     * Find an entity by its primary key or return fresh entity instance.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return mixed
     */
    public function findOrNew($id, $attributes = ['*']);

    /**
     * Find an entity by one of its attributes.
     *
     * @param string $attribute
     * @param string $value
     * @param array  $attributes
     *
     * @return mixed
     */
    public function findBy($attribute, $value, $attributes = ['*']);

    /**
     * Find the first entity.
     *
     * @param array $attributes
     *
     * @return mixed
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
     * @param bool  $syncRelations
     *
     * @return mixed
     */
    public function create(array $attributes = [], bool $syncRelations = false);

    /**
     * Update an entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     * @param bool  $syncRelations
     *
     * @return mixed
     */
    public function update($id, array $attributes = [], bool $syncRelations = false);

    /**
     * Store the entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     * @param bool  $syncRelations
     *
     * @return mixed
     */
    public function store($id, array $attributes = [], bool $syncRelations = false);

    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function delete($id);

    /**
     * Restore an entity with the given id.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function restore($id);

    /**
     * Start a new database transaction.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack(): void;

    /**
     * Retrieve the "count" result of the query.
     *
     * @param string $columns
     *
     * @return int
     */
    public function count($columns = '*'): int;

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function min($column);

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function max($column);

    /**
     * Retrieve the average value of a given column.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function avg($column);

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string $column
     *
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
