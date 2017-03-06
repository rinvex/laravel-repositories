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

declare(strict_types=1);

namespace Rinvex\Repository\Traits;

trait Bindable
{
    /**
     * The repository alias pattern.
     *
     * @var string
     */
    protected $repositoryAliasPattern = '{{class}}Contract';

    /**
     * Register an IoC binding whether it's already been registered or not.
     *
     * @param string               $abstract
     * @param \Closure|string|null $concrete
     * @param bool                 $shared
     * @param string|null          $alias
     * @param bool                 $force
     *
     * @return void
     */
    protected function bindRepository($abstract, $concrete = null, $shared = true, $alias = null, $force = false)
    {
        if (! $this->app->bound($abstract) || $force) {
            $concrete = $concrete ?: $abstract;
            $this->app->bind($abstract, $concrete, $shared);
            $this->app->alias($abstract, $this->prepareRepositoryAlias($alias, $concrete));
        }
    }

    /**
     * Prepare the repository alias.
     *
     * @param string|null $alias
     * @param mixed       $concrete
     *
     * @return string
     */
    protected function prepareRepositoryAlias($alias, $concrete)
    {
        if (! $alias && ! $concrete instanceof \Closure) {
            $concrete = str_replace('Repositories', 'Contracts', $concrete);
            $alias = str_replace('{{class}}', $concrete, $this->repositoryAliasPattern);
        }

        return $alias;
    }
}
