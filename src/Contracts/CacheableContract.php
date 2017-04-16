<?php

declare(strict_types=1);

namespace Rinvex\Repository\Contracts;

interface CacheableContract
{
    /**
     * Set the repository cache lifetime.
     *
     * @param float|int $cacheLifetime
     *
     * @return $this
     */
    public function setCacheLifetime($cacheLifetime);

    /**
     * Get the repository cache lifetime.
     *
     * @return float|int
     */
    public function getCacheLifetime();

    /**
     * Set the repository cache driver.
     *
     * @param string $cacheDriver
     *
     * @return $this
     */
    public function setCacheDriver($cacheDriver);

    /**
     * Get the repository cache driver.
     *
     * @return string
     */
    public function getCacheDriver();

    /**
     * Enable repository cache clear.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function enableCacheClear($status);

    /**
     * Determine if repository cache clear is enabled.
     *
     * @return bool
     */
    public function isCacheClearEnabled();

    /**
     * Forget the repository cache.
     *
     * @return $this
     */
    public function forgetCache();
}
