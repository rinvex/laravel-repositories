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
     * @param array $attributes
     *
     * @return object
     */
    public function find($id, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($id, $attributes) {
            return $this->model->find($id, $attributes);
        });
    }

    /**
     * Find an entity by one of it's attributes.
     *
     * @param string $attribute
     * @param string $value
     * @param array  $attributes
     *
     * @return object
     */
    public function findBy($attribute, $value, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $value, $attributes) {
            return $this->model->where($attribute, '=', $value)->first($attributes);
        });
    }

    /**
     * Find all entities.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAll($attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attributes) {
            return $this->model->get($attributes);
        });
    }

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
    public function paginate($perPage = null, $attributes = ['*'], $pageName = 'page', $page = null)
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($perPage, $attributes, $pageName, $page) {
            return $this->model->paginate($perPage, $attributes, $pageName, $page);
        });
    }

    /**
     * Paginate all entities into a simple paginator.
     *
     * @param  int    $perPage
     * @param  array  $attributes
     * @param  string $pageName
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $attributes = ['*'], $pageName = 'page')
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($perPage, $attributes, $pageName) {
            return $this->prepareQuery($this->createModel())->simplePaginate($perPage, $attributes, $pageName);
        });
    }

    /**
     * Find all entities matching where conditions.
     *
     * @param array $where
     * @param array $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhere(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($where, $attributes) {
            foreach ($where as $attribute => $value) {
                if (is_array($value)) {
                    list($attribute, $condition, $value) = $value;
                    $this->model = $this->model->where($attribute, $condition, $value);
                } else {
                    $this->model = $this->model->where($attribute, '=', $value);
                }
            }

            return $this->model->get($attributes);
        });
    }

    /**
     * Find all entities matching whereIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereIn($attribute, array $values, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $values, $attributes) {
            return $this->model->whereIn($attribute, $values)->get($attributes);
        });
    }

    /**
     * Find all entities matching whereNotIn conditions.
     *
     * @param string $attribute
     * @param array  $values
     * @param array  $attributes
     *
     * @return \Illuminate\Support\Collection
     */
    public function findWhereNotIn($attribute, array $values, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $values, $attributes) {
            return $this->model->whereNotIn($attribute, $values)->get($attributes);
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
