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

interface TransactionableContract
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
    public function create(array $attributes = []);

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
    public function update($id, array $attributes = []);

    /**
     * Delete an entity with the given id.
     *
     * @param mixed $id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function delete($id);

    /**
     * Start a new database transaction.
     *
     * @return void
     * @throws Exception
     */
    public function beginTransaction();

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit();


    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack();
}
