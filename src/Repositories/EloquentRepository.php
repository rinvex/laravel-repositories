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

use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Repository\Exceptions\RepositoryException;

class EloquentRepository extends BaseRepository
{
    /**
     * {@inheritdoc}
     */
    public function createModel()
    {
        if (is_string($model = $this->getModel())) {
            if (! class_exists($class = '\\'.ltrim($model, '\\'))) {
                throw new RepositoryException("Class {$model} does NOT exist!");
            }

            $model = $this->getContainer()->make($class);
        }

        // Set the connection used by the model
        if (! empty($this->connection)) {
            $model = $model->setConnection($this->connection);
        }

        if (! $model instanceof Model) {
            throw new RepositoryException("Class {$model} must be an instance of \\Illuminate\\Database\\Eloquent\\Model");
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($id, $attributes) {
            return $this->prepareQuery($this->createModel())->find($id, $attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($attribute, $value, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attribute, $value, $attributes) {
            return $this->prepareQuery($this->createModel())->where($attribute, '=', $value)->first($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findFirst($attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attributes) {
            return $this->prepareQuery($this->createModel())->first($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findAll($attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($attributes) {
            return $this->prepareQuery($this->createModel())->get($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $attributes = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        return $this->executeCallback(get_called_class(), __FUNCTION__, array_merge(func_get_args(), compact('page')), function () use ($perPage, $attributes, $pageName, $page) {
            return $this->prepareQuery($this->createModel())->paginate($perPage, $attributes, $pageName, $page);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function simplePaginate($perPage = null, $attributes = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        return $this->executeCallback(get_called_class(), __FUNCTION__, array_merge(func_get_args(), compact('page')), function () use ($perPage, $attributes, $pageName, $page) {
            return $this->prepareQuery($this->createModel())->simplePaginate($perPage, $attributes, $pageName, $page);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findWhere(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($where, $attributes) {
            list($attribute, $operator, $value, $boolean) = array_pad($where, 4, null);

            $this->where($attribute, $operator, $value, $boolean);

            return $this->prepareQuery($this->createModel())->get($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereIn(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($where, $attributes) {
            list($attribute, $values, $boolean, $not) = array_pad($where, 4, null);

            $this->whereIn($attribute, $values, $boolean, $not);

            return $this->prepareQuery($this->createModel())->get($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereNotIn(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($where, $attributes) {
            list($attribute, $values, $boolean) = array_pad($where, 3, null);

            $this->whereNotIn($attribute, $values, $boolean);

            return $this->prepareQuery($this->createModel())->get($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereHas(array $where, $attributes = ['*'])
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($where, $attributes) {
            list($relation, $callback, $operator, $count) = array_pad($where, 4, null);

            $this->whereHas($relation, $callback, $operator, $count);

            return $this->prepareQuery($this->createModel())->get($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $attributes = [])
    {
        // Create a new instance
        $instance = $this->createModel();

        // Fire the created event
        $this->getContainer('events')->fire($this->getRepositoryId().'.entity.creating', [$this, $instance]);

        // Fill instance with data
        $instance->fill($attributes);

        // Save the instance
        $created = $instance->save();

        // Fire the created event
        $this->getContainer('events')->fire($this->getRepositoryId().'.entity.created', [$this, $instance]);

        // Return instance
        return $created ? $instance : $created;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $attributes = [])
    {
        $updated = false;

        // Find the given instance
        $instance = $id instanceof Model ? $id : $this->find($id);

        if ($instance) {
            // Fire the updated event
            $this->getContainer('events')->fire($this->getRepositoryId().'.entity.updating', [$this, $instance]);

            // Fill instance with data
            $instance->fill($attributes);

            // Update the instance
            $updated = $instance->save();

            // Fire the updated event
            $this->getContainer('events')->fire($this->getRepositoryId().'.entity.updated', [$this, $instance]);
        }

        return $updated ? $instance : $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $deleted  = false;

        // Find the given instance
        $instance = $id instanceof Model ? $id : $this->find($id);

        if ($instance) {
            // Fire the deleted event
            $this->getContainer('events')->fire($this->getRepositoryId().'.entity.deleting', [$this, $instance]);

            // Delete the instance
            $deleted = $instance->delete();

            // Fire the deleted event
            $this->getContainer('events')->fire($this->getRepositoryId().'.entity.deleted', [$this, $instance]);
        }

        return $deleted ? $instance : $deleted;
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
    public function count($columns = '*')
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($columns) {
            return $this->prepareQuery($this->createModel())->count($columns);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function min($column)
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($column) {
            return $this->prepareQuery($this->createModel())->min($column);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function max($column)
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($column) {
            return $this->prepareQuery($this->createModel())->max($column);
        });
    }
}
