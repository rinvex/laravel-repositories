<?php

declare(strict_types=1);

namespace Rinvex\Repository\Repositories;

use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Rinvex\Repository\Exceptions\RepositoryException;
use Rinvex\Repository\Exceptions\EntityNotFoundException;

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
    public function findOrFail($id, $attributes = ['*'])
    {
        $result = $this->find($id, $attributes);

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        throw new EntityNotFoundException($this->getModel(), $id);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrNew($id, $attributes = ['*'])
    {
        if (! is_null($entity = $this->find($id, $attributes))) {
            return $entity;
        }

        return $this->createModel();
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
            [$attribute, $operator, $value, $boolean] = array_pad($where, 4, null);

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
            [$attribute, $values, $boolean, $not] = array_pad($where, 4, null);

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
            [$attribute, $values, $boolean] = array_pad($where, 3, null);

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
            [$relation, $callback, $operator, $count] = array_pad($where, 4, null);

            $this->whereHas($relation, $callback, $operator, $count);

            return $this->prepareQuery($this->createModel())->get($attributes);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $attributes = [], bool $syncRelations = false)
    {
        // Create a new instance
        $entity = $this->createModel();

        // Fire the created event
        $this->getContainer('events')->dispatch($this->getRepositoryId().'.entity.creating', [$this, $entity]);

        // Extract relationships
        if ($syncRelations) {
            $relations = $this->extractRelations($entity, $attributes);
            array_forget($attributes, array_keys($relations));
        }

        // Fill instance with data
        $entity->fill($attributes);

        // Save the instance
        $created = $entity->save();

        // Sync relationships
        if ($syncRelations && isset($relations)) {
            $this->syncRelations($entity, $relations);
        }

        // Fire the created event
        $this->getContainer('events')->dispatch($this->getRepositoryId().'.entity.created', [$this, $entity]);

        // Return instance
        return $created ? $entity : $created;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $attributes = [], bool $syncRelations = false)
    {
        $updated = false;

        // Find the given instance
        $entity = $id instanceof Model ? $id : $this->find($id);

        if ($entity) {
            // Fire the updated event
            $this->getContainer('events')->dispatch($this->getRepositoryId().'.entity.updating', [$this, $entity]);

            // Extract relationships
            if ($syncRelations) {
                $relations = $this->extractRelations($entity, $attributes);
                array_forget($attributes, array_keys($relations));
            }

            // Fill instance with data
            $entity->fill($attributes);

            //Check if we are updating attributes values
            $dirty = $entity->getDirty();

            // Update the instance
            $updated = $entity->save();

            // Sync relationships
            if ($syncRelations && isset($relations)) {
                $this->syncRelations($entity, $relations);
            }

            if (count($dirty) > 0) {
                // Fire the updated event
                $this->getContainer('events')->dispatch($this->getRepositoryId().'.entity.updated', [$this, $entity]);
            }
        }

        return $updated ? $entity : $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $deleted = false;

        // Find the given instance
        $entity = $id instanceof Model ? $id : $this->find($id);

        if ($entity) {
            // Fire the deleted event
            $this->getContainer('events')->dispatch($this->getRepositoryId().'.entity.deleting', [$this, $entity]);

            // Delete the instance
            $deleted = $entity->delete();

            // Fire the deleted event
            $this->getContainer('events')->dispatch($this->getRepositoryId().'.entity.deleted', [$this, $entity]);
        }

        return $deleted ? $entity : $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function restore($id)
    {
        $restored = false;

        // Find the given instance
        $entity = $id instanceof Model ? $id : $this->find($id);

        if ($entity) {
            // Fire the restoring event
            $this->getContainer('events')->dispatch($this->getRepositoryId().'.entity.restoring', [$this, $entity]);

            // Restore the instance
            $restored = $entity->restore();

            // Fire the restored event
            $this->getContainer('events')->dispatch($this->getRepositoryId().'.entity.restored', [$this, $entity]);
        }

        return $restored ? $entity : $restored;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
        $this->getContainer('db')->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        $this->getContainer('db')->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack(): void
    {
        $this->getContainer('db')->rollBack();
    }

    /**
     * {@inheritdoc}
     */
    public function count($columns = '*'): int
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

    /**
     * {@inheritdoc}
     */
    public function avg($column)
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($column) {
            return $this->prepareQuery($this->createModel())->avg($column);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function sum($column)
    {
        return $this->executeCallback(get_called_class(), __FUNCTION__, func_get_args(), function () use ($column) {
            return $this->prepareQuery($this->createModel())->sum($column);
        });
    }

    /**
     * Extract relationships.
     *
     * @param mixed $entity
     * @param array $attributes
     *
     * @return array
     */
    protected function extractRelations($entity, array $attributes): array
    {
        $relations = [];
        $potential = array_diff(array_keys($attributes), $entity->getFillable());

        array_walk($potential, function ($relation) use ($entity, $attributes, &$relations) {
            if (method_exists($entity, $relation)) {
                $relations[$relation] = [
                    'values' => $attributes[$relation],
                    'class' => get_class($entity->{$relation}()),
                ];
            }
        });

        return $relations;
    }

    /**
     * Sync relationships.
     *
     * @param mixed $entity
     * @param array $relations
     * @param bool  $detaching
     *
     * @return void
     */
    protected function syncRelations($entity, array $relations, $detaching = true): void
    {
        foreach ($relations as $method => $relation) {
            switch ($relation['class']) {
                case 'Illuminate\Database\Eloquent\Relations\BelongsToMany':
                default:
                    $entity->{$method}()->sync((array) $relation['values'], $detaching);
                    break;
            }
        }
    }
}
