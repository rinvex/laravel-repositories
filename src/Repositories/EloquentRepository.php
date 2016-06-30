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
     * Create a new repository model instance.
     *
     * @throws \Rinvex\Repository\Exceptions\RepositoryException
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        if (is_string($model = $this->getModel())) {
            if (! class_exists($class = '\\'.ltrim($model, '\\'))) {
                throw new RepositoryException("Class {$model} does NOT exist!");
            }

            $model = $this->getContainer()->make($class);
        }

        if (! $model instanceof Model) {
            throw new RepositoryException("Class {$model} must be an instance of \\Illuminate\\Database\\Eloquent\\Model");
        }

        return $model;
    }

    /**
     * Find an entity by its primary key.
     *
     * @param int   $id
     * @param array $columns
     *
     * @return object
     */
    public function find($id, $columns = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($id, $columns) {
            return $this->model->find($id, $columns);
        });
    }

    /**
     * Find an entity by one of it's attributes.
     *
     * @param string $attribute
     * @param string $value
     * @param array  $columns
     *
     * @return object
     */
    public function findBy($attribute, $value, $columns = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $value, $columns) {
            return $this->model->where($attribute, '=', $value)->first($columns);
        });
    }

    /**
     * Find all entities.
     *
     * @param array $columns
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAll($columns = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($columns) {
            return $this->model->get($columns);
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
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($where, $columns) {
            foreach ($where as $attribute => $value) {
                if (is_array($value)) {
                    list($attribute, $condition, $value) = $value;
                    $this->model = $this->model->where($attribute, $condition, $value);
                } else {
                    $this->model = $this->model->where($attribute, '=', $value);
                }
            }

            return $this->model->get($columns);
        });
    }

    /**
     * Find all entities matching whereIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $columns
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereIn($attribute, array $values, $columns = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $values, $columns) {
            return $this->model->whereIn($attribute, $values)->get($columns);
        });
    }

    /**
     * Find all entities matching whereNotIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $columns
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereNotIn($attribute, array $values, $columns = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $values, $columns) {
            return $this->model->whereNotIn($attribute, $values)->get($columns);
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
