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
     * @return array
     * @throws \Exception
     */
    public function create(array $attributes = [])
    {
        // Start transaction!
        $this->getContainer('db')->beginTransaction();
        try {
            $result = parent::create($attributes);
        } catch (\Exception $e) {
            // Rollback if something went wrong
            $this->getContainer('db')->rollback();
            throw $e;
        }
        // Commit the queries!
        $this->getContainer('db')->commit();

        return $result;
    }

    /**
     * Update an entity with the given attributes.
     *
     * @param mixed $id
     * @param array $attributes
     *
     * @return array
     * @throws \Exception
     */
    public function update($id, array $attributes = [])
    {
        // Start transaction!
        $this->getContainer('db')->beginTransaction();
        try {
            $result = parent::update($id, $attributes);
        } catch (\Exception $e) {
            // Rollback if something went wrong
            $this->getContainer('db')->rollback();
            throw $e;
        }
        // Commit the queries!
        $this->getContainer('db')->commit();

        return $result;
    }

    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @return array
     * @throws \Exception
     */
    public function delete($id)
    {
        // Start transaction!
        $this->getContainer('db')->beginTransaction();
        try {
            $result = parent::delete($id);
        } catch (\Exception $e) {
            // Rollback if something went wrong
            $this->getContainer('db')->rollback();
            throw $e;
        }
        // Commit the queries!
        $this->getContainer('db')->commit();

        return $result;
    }
}
