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

use Illuminate\Contracts\Container\Container;

interface ContainerContract
{
    /**
     * Set the IoC container instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container);
    
    /**
     * Return the IoC container instance or any of it's services.
     *
     * @param String $service
     *
     * @return mixed
     */
    public function getContainer();
}
