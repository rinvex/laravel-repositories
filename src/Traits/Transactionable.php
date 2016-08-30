<?php

/*
*
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

namespace Rinvex\Repository\Traits;

trait Transactionable
{
    /**
     * Create a new entity with the given attributes.
     *
     * @param array $attributes
     *
     * @throws \Exception
     *
     * @return array
     */
    public function create(array $attributes = [])
    {
        // Start transaction!
        $this->beginTransaction();
        try {
            $result = parent::create($attributes);
        } catch (\Exception $e) {
            // Rollback if something went wrong
            $this->rollback();
            throw $e;
        }
        // Commit the queries!
        $this->commit();

        return $result;
    }

    /**
     * Update an entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @throws \Exception
     *
     * @return array
     */
    public function update($id, array $attributes = [])
    {
        // Start transaction!
        $this->beginTransaction();
        try {
            $result = parent::update($id, $attributes);
        } catch (\Exception $e) {
            // Rollback if something went wrong
            $this->rollback();
            throw $e;
        }
        // Commit the queries!
        $this->commit();

        return $result;
    }

    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function delete($id)
    {
        // Start transaction!
        $this->beginTransaction();
        try {
            $result = parent::delete($id);
        } catch (\Exception $e) {
            // Rollback if something went wrong
            $this->rollback();
            throw $e;
        }
        // Commit the queries!

        return $result;
    }

    /**
     * Start a new database transaction.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->getContainer('db')->beginTransaction();
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->getContainer('db')->commit();
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        $this->getContainer('db')->rollback();
    }
}
