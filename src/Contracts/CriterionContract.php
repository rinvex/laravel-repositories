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

namespace Rinvex\Repository\Contracts;

interface CriterionContract
{
    /**
     * Apply current criterion to the given query and return query.
     *
     * @param mixed              $query
     * @param RepositoryContract $repository
     *
     * @return mixed
     */
    public function apply($query, $repository);
}
