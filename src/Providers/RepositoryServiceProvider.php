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

namespace Rinvex\Repository\Providers;

use Illuminate\Support\ServiceProvider;
use Rinvex\Repository\Listeners\RepositoryEventListener;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * The repository alias pattern.
     *
     * @var string
     */
    protected $repositoryAliasPattern = '{{class}}Contract';

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.repository');

        // Register the event listener
        $this->app->bind('rinvex.repository.listener', RepositoryEventListener::class);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                realpath(__DIR__.'/../../config/config.php') => config_path('rinvex.repository.php'),
            ], 'config');
        }

        // Subscribe the registered event listener
        $this->app['events']->subscribe('rinvex.repository.listener');
    }
}
