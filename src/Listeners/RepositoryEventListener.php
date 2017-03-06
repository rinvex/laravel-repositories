<?php

declare(strict_types=1);
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
     * @param string $eventName
     * @param array  $data
     *
     * @return void
     */
    public function entityCreating($eventName, $data)
    {
        //
    }

    /**
     * Listen to entities created.
     *
     * @param string $eventName
     * @param array  $data
     *
     * @return void
     */
    public function entityCreated($eventName, $data)
    {
        $clearOn = $data[0]->getContainer('config')->get('rinvex.repository.cache.clear_on');

        if ($data[0]->isCacheClearEnabled() && in_array('create', $clearOn)) {
            $data[0]->forgetCache();
        }
    }

    /**
     * Listen to entities being updated.
     *
     * @param string $eventName
     * @param array  $data
     *
     * @return void
     */
    public function entityUpdating($eventName, $data)
    {
        //
    }

    /**
     * Listen to entities updated.
     *
     * @param string $eventName
     * @param array  $data
     *
     * @return void
     */
    public function entityUpdated($eventName, $data)
    {
        $clearOn = $data[0]->getContainer('config')->get('rinvex.repository.cache.clear_on');

        if ($data[0]->isCacheClearEnabled() && in_array('update', $clearOn)) {
            $data[0]->forgetCache();
        }
    }

    /**
     * Listen to entities being deleted.
     *
     * @param string $eventName
     * @param array  $data
     *
     * @return void
     */
    public function entityDeleting($eventName, $data)
    {
        //
    }

    /**
     * Listen to entities deleted.
     *
     * @param string $eventName
     * @param array  $data
     *
     * @return void
     */
    public function entityDeleted($eventName, $data)
    {
        $clearOn = $data[0]->getContainer('config')->get('rinvex.repository.cache.clear_on');

        if ($data[0]->isCacheClearEnabled() && in_array('delete', $clearOn)) {
            $data[0]->forgetCache();
        }
    }
}
