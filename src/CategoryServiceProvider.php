<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Rinvex Category Package.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Rinvex Category Package
 * License: The MIT License (MIT)
 * Link:    https://rinvex.com
 */

declare(strict_types=1);

namespace Rinvex\Category;

use Illuminate\Support\ServiceProvider;

class CategoryServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(realpath(__DIR__.'/../config/config.php'), 'rinvex.category');
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // Load migrations
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            // Publish migrations
            $this->publishes([
                realpath(__DIR__.'/../database/migrations') => database_path('migrations'),
            ], 'migrations');

            // Publish config
            $this->publishes([
                realpath(__DIR__.'/../config/config.php') => config_path('rinvex.category.php'),
            ], 'config');
        }
    }
}
