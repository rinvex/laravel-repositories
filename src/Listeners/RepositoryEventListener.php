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

namespace Rinvex\Repository\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Rinvex\Repository\Contracts\RepositoryContract;

class RepositoryEventListener
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('*.entity.creating', __CLASS__.'@entityCreating');
        $dispatcher->listen('*.entity.created', __CLASS__.'@entityCreated');
        $dispatcher->listen('*.entity.updating', __CLASS__.'@entityUpdating');
        $dispatcher->listen('*.entity.updated', __CLASS__.'@entityUpdated');
        $dispatcher->listen('*.entity.deleting', __CLASS__.'@entityDeleting');
        $dispatcher->listen('*.entity.deleted', __CLASS__.'@entityDeleted');
    }

    /**
     * Listen to entities being created.
     *
     * @param \Rinvex\Repository\Contracts\RepositoryContract $repository
     * @param mixed                                           $entity
     *
     * @return void
     */
    public function entityCreating(RepositoryContract $repository, $entity)
    {
        //
    }

    /**
     * Listen to entities created.
     *
     * @param \Rinvex\Repository\Contracts\RepositoryContract $repository
     * @param mixed                                           $entity
     *
     * @return void
     */
    public function entityCreated(RepositoryContract $repository, $entity)
    {
        $clearOn = $repository->getContainer('config')->get('rinvex.repository.cache.clear_on');

        if ($repository->isCacheClearEnabled() && in_array('create', $clearOn)) {
            $repository->forgetCache();
        }
    }

    /**
     * Listen to entities being updated.
     *
     * @param \Rinvex\Repository\Contracts\RepositoryContract $repository
     * @param mixed                                           $entity
     *
     * @return void
     */
    public function entityUpdating(RepositoryContract $repository, $entity)
    {
        //
    }

    /**
     * Listen to entities updated.
     *
     * @param \Rinvex\Repository\Contracts\RepositoryContract $repository
     * @param mixed                                           $entity
     *
     * @return void
     */
    public function entityUpdated(RepositoryContract $repository, $entity)
    {
        $clearOn = $repository->getContainer('config')->get('rinvex.repository.cache.clear_on');

        if ($repository->isCacheClearEnabled() && in_array('update', $clearOn)) {
            $repository->forgetCache();
        }
    }

    /**
     * Listen to entities being deleted.
     *
     * @param \Rinvex\Repository\Contracts\RepositoryContract $repository
     * @param mixed                                           $entity
     *
     * @return void
     */
    public function entityDeleting(RepositoryContract $repository, $entity)
    {
        //
    }

    /**
     * Listen to entities deleted.
     *
     * @param \Rinvex\Repository\Contracts\RepositoryContract $repository
     * @param mixed                                           $entity
     *
     * @return void
     */
    public function entityDeleted(RepositoryContract $repository, $entity)
    {
        $clearOn = $repository->getContainer('config')->get('rinvex.repository.cache.clear_on');

        if ($repository->isCacheClearEnabled() && in_array('delete', $clearOn)) {
            $repository->forgetCache();
        }
    }
}
