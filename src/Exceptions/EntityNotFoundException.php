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

namespace Rinvex\Repository\Exceptions;

use RuntimeException;

class EntityNotFoundException extends RuntimeException
{
    /**
     * Id of the affected model.
     *
     * @var string
     */
    protected $id;

    /**
     * Name of the affected model.
     *
     * @var string
     */
    protected $model;

    /**
     * Set the affected model.
     *
     * @param string $model
     * @param int    $id
     *
     * @return void
     */
    public function __construct($model, $id)
    {
        $this->id = $id;
        $this->model = $model;
        $this->message = "No results for model [{$model}] #{$id}.";
    }

    /**
     * Get the affected model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get the affected model Id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
