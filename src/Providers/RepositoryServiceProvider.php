<?php

declare(strict_types=1);

namespace Rinvex\Repository\Providers;

use Illuminate\Support\ServiceProvider;
use Rinvex\Repository\Listeners\RepositoryEventListener;
use Rinvex\Support\Traits\ConsoleTools;

class RepositoryServiceProvider extends ServiceProvider
{
    use ConsoleTools;

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
        // Publish config
        $this->publishesConfig('rinvex/repositories');

        // Subscribe the registered event listener
        $this->app['events']->subscribe('rinvex.repository.listener');
    }
}
