<?php

declare(strict_types=1);

namespace Rinvex\Repository\Traits;

use Closure;

trait Cacheable
{
    /**
     * The repository cache lifetime.
     *
     * @var int
     */
    protected $cacheLifetime;

    /**
     * The repository cache driver.
     *
     * @var string
     */
    protected $cacheDriver;

    /**
     * Indicate if the repository cache clear is enabled.
     *
     * @var bool
     */
    protected $cacheClearEnabled = true;

    /**
     * Generate unique query hash.
     *
     * @param $args
     *
     * @return string
     */
    protected function generateCacheHash($args): string
    {
        return md5(json_encode($args + [
                $this->getRepositoryId(),
                $this->getModel(),
                $this->getCacheDriver(),
                $this->getCacheLifetime(),
                $this->relations,
                $this->where,
                $this->whereIn,
                $this->whereNotIn,
                $this->offset,
                $this->limit,
                $this->orderBy,
            ]));
    }

    /**
     * Store cache keys by mimicking cache tags.
     *
     * @param string $class
     * @param string $method
     * @param string $hash
     *
     * @return void
     */
    protected function storeCacheKeys($class, $method, $hash): void
    {
        $keysFile = $this->getContainer('config')->get('rinvex.repository.cache.keys_file');
        $cacheKeys = $this->getCacheKeys($keysFile);

        if (! isset($cacheKeys[$class]) || ! in_array($method.'.'.$hash, $cacheKeys[$class])) {
            $cacheKeys[$class][] = $method.'.'.$hash;
            file_put_contents($keysFile, json_encode($cacheKeys));
        }
    }

    /**
     * Get cache keys.
     *
     * @param string $file
     *
     * @return array
     */
    protected function getCacheKeys($file): array
    {
        if (! file_exists($file)) {
            file_put_contents($file, null);
        }

        return json_decode(file_get_contents($file), true) ?: [];
    }

    /**
     * Flush cache keys by mimicking cache tags.
     *
     * @return array
     */
    protected function flushCacheKeys(): array
    {
        $flushedKeys = [];
        $calledClass = get_called_class();
        $config = $this->getContainer('config')->get('rinvex.repository.cache');
        $cacheKeys = $this->getCacheKeys($config['keys_file']);

        if (isset($cacheKeys[$calledClass]) && is_array($cacheKeys[$calledClass])) {
            foreach ($cacheKeys[$calledClass] as $cacheKey) {
                $flushedKeys[] = $calledClass.'@'.$cacheKey;
            }

            unset($cacheKeys[$calledClass]);
            file_put_contents($config['keys_file'], json_encode($cacheKeys));
        }

        return $flushedKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheLifetime($cacheLifetime)
    {
        $this->cacheLifetime = $cacheLifetime;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheLifetime(): int
    {
        // Return value even if it's zero "0" (which means cache is disabled)
        return $this->cacheLifetime ?? $this->getContainer('config')->get('rinvex.repository.cache.lifetime');
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheDriver($cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDriver(): ?string
    {
        return $this->cacheDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function enableCacheClear($status = true)
    {
        $this->cacheClearEnabled = $status;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isCacheClearEnabled(): bool
    {
        return $this->cacheClearEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function forgetCache()
    {
        if ($this->getCacheLifetime()) {
            // Flush cache tags
            if (method_exists($this->getContainer('cache')->getStore(), 'tags')) {
                $this->getContainer('cache')->tags($this->getRepositoryId())->flush();
            } else {
                // Flush cache keys, then forget actual cache
                foreach ($this->flushCacheKeys() as $cacheKey) {
                    $this->getContainer('cache')->forget($cacheKey);
                }
            }

            $this->getContainer('events')->fire($this->getRepositoryId().'.entity.cache.flushed', [$this]);
        }

        return $this;
    }

    /**
     * Cache given callback.
     *
     * @param string   $class
     * @param string   $method
     * @param array    $args
     * @param \Closure $closure
     *
     * @return mixed
     */
    protected function cacheCallback($class, $method, $args, Closure $closure)
    {
        $repositoryId = $this->getRepositoryId();
        $lifetime = $this->getCacheLifetime();
        $hash = $this->generateCacheHash($args);
        $cacheKey = $class.'@'.$method.'.'.$hash;

        // Switch cache driver on runtime
        if ($driver = $this->getCacheDriver()) {
            $this->getContainer('cache')->setDefaultDriver($driver);
        }

        // We need cache tags, check if default driver supports it
        if (method_exists($this->getContainer('cache')->getStore(), 'tags')) {
            $result = $lifetime === -1
                ? $this->getContainer('cache')->tags($repositoryId)->rememberForever($cacheKey, $closure)
                : $this->getContainer('cache')->tags($repositoryId)->remember($cacheKey, $lifetime, $closure);

            // We're done, let's clean up!
            $this->resetRepository();

            return $result;
        }

        // Default cache driver doesn't support tags, let's do it manually
        $this->storeCacheKeys($class, $method, $hash);

        $result = $lifetime === -1
            ? $this->getContainer('cache')->rememberForever($cacheKey, $closure)
            : $this->getContainer('cache')->remember($cacheKey, $lifetime, $closure);

        // We're done, let's clean up!
        $this->resetCachedRepository();

        return $result;
    }

    /**
     * Reset cached repository to its defaults.
     *
     * @return $this
     */
    protected function resetCachedRepository()
    {
        $this->resetRepository();

        $this->cacheLifetime = null;
        $this->cacheDriver = null;

        return $this;
    }
}
