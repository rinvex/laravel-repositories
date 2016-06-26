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

use Illuminate\Database\Eloquent\Model;
use Rinvex\Repository\Exceptions\RepositoryException;

class EloquentRepository extends BaseRepository
{
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
    public function retrieveModel($model = null, array $data = [])
    {
        if (is_null($model)) {
            $model = $this->model ?: str_replace(['Repositories', 'Repository'], ['Models', ''], get_called_class());
        }

        if (is_string($model)) {
            if (! class_exists($class = '\\'.ltrim($model, '\\'))) {
                throw new RepositoryException("Class {$model} does NOT exist!");
            }

            $model = $this->getContainer()->make($class, [$data]);
        }

        if (! $model instanceof Model) {
            throw new RepositoryException("Class {$model} must be an instance of \\Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Find an entity by its primary key.
     *
     * @param int   $id
     * @param array $columns
     * @param array $with
     *
     * @return object
     */
    public function find($id, $columns = ['*'], $with = [])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($id, $columns, $with) {
            return $this->model->with($with)->find($id, $columns);
        });
    }

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
    public function findBy($attribute, $value, $columns = ['*'], $with = [])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $value, $columns, $with) {
            return $this->model->with($with)->where($attribute, '=', $value)->first($columns);
        });
    }

    /**
     * Find all entities.
     *
     * @param array $columns
     * @param array $with
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAll($columns = ['*'], $with = [])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($columns, $with) {
            return $this->model->with($with)->get($columns);
        });
    }

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
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($perPage, $columns, $pageName, $page) {
            return $this->model->paginate($perPage, $columns, $pageName, $page);
        });
    }

    /**
     * Find all entities matching where conditions.
     *
     * @param array $where
     * @param array $columns
     * @param array $with
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhere(array $where, $columns = ['*'], $with = [])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($where, $columns, $with) {
            foreach ($where as $attribute => $value) {
                if (is_array($value)) {
                    list($attribute, $condition, $value) = $value;
                    $this->model = $this->model->where($attribute, $condition, $value);
                } else {
                    $this->model = $this->model->where($attribute, '=', $value);
                }
            }

            return $this->model->with($with)->get($columns);
        });
    }

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
    public function findWhereIn($attribute, array $values, $columns = ['*'], $with = [])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $values, $columns, $with) {
            return $this->model->with($with)->whereIn($attribute, $values)->get($columns);
        });
    }

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
    public function findWhereNotIn($attribute, array $values, $columns = ['*'], $with = [])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $values, $columns, $with) {
            return $this->model->with($with)->whereNotIn($attribute, $values)->get($columns);
        });
    }

    /**
     * Create a new entity with the given attributes.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function create(array $attributes = [])
    {
        // Create a new instance
        $instance = $this->model->newInstance($attributes);

        // Save the instance
        $created = $instance->save();

        // Fire the created event
        $this->getContainer('events')->fire($this->getRepositoryId().'.entity.created', [$this, $instance]);

        // Return instance
        return [
            $created,
            $instance,
        ];
    }

    /**
     * Find entity matching the given attributes or create it.
     *
     * @param array $attributes
     *
     * @return object|array
     */
    public function findOrCreate(array $attributes)
    {
        if (! is_null($instance = $this->findWhere($attributes)->first())) {
            return $instance;
        }

        return $this->create($attributes);
    }

    /**
     * Update an entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return array
     */
    public function update($id, array $attributes = [])
    {
        // Find the given instance
        $updated  = false;
        $instance = $id instanceof Model ? $id : $this->find($id);

        if ($instance) {
            // Update the instance
            $updated = $instance->update($attributes);

            // Fire the updated event
            $this->getContainer('events')->fire($this->getRepositoryId().'.entity.updated', [$this, $instance]);
        }

        return [
            $updated,
            $instance,
        ];
    }

    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @return array
     */
    public function delete($id)
    {
        // Find the given instance
        $deleted  = false;
        $instance = $id instanceof Model ? $id : $this->find($id);

        if ($instance) {
            // Delete the instance
            $deleted = $instance->delete();

            // Fire the deleted event
            $this->getContainer('events')->fire($this->getRepositoryId().'.entity.deleted', [$this, $instance]);
        }

        return [
            $deleted,
            $instance,
        ];
    }
}
