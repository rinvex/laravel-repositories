<?php

declare(strict_types=1);

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
        $dispatcher->listen('*.entity.creating', self::class.'@entityCreating');
        $dispatcher->listen('*.entity.created', self::class.'@entityCreated');
        $dispatcher->listen('*.entity.updating', self::class.'@entityUpdating');
        $dispatcher->listen('*.entity.updated', self::class.'@entityUpdated');
        $dispatcher->listen('*.entity.deleting', self::class.'@entityDeleting');
        $dispatcher->listen('*.entity.deleted', self::class.'@entityDeleted');
    }

    /**
     * Listen to entities being created.
     *
     * @param string $eventName
     * @param array  $data
     *
     * @return void
     */
    public function entityCreating($eventName, $data): void
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
    public function entityCreated($eventName, $data): void
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
    public function entityUpdating($eventName, $data): void
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
    public function entityUpdated($eventName, $data): void
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
    public function entityDeleting($eventName, $data): void
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
    public function entityDeleted($eventName, $data): void
    {
        $clearOn = $data[0]->getContainer('config')->get('rinvex.repository.cache.clear_on');

        if ($data[0]->isCacheClearEnabled() && in_array('delete', $clearOn)) {
            $data[0]->forgetCache();
        }
    }
}
