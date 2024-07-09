<?php

namespace Itsemon245\PausableJob;

use Illuminate\Queue\QueueServiceProvider;
use Itsemon245\PausableJob\Queue\Connector\PausableDatabaseConnector;

/**
 * PausableJob package service provider
 * @author Mojahidul Islam <itsemon245@gmail.com>
 */
class PausableJobServiceProvider extends QueueServiceProvider
{
    /**
    * Bootstrap services.
    */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish the migration if not migrated
            $path = database_path("migrations/*_pausable_job_migration.php");
            $isPublished = shell_exec("ls $path");
            if (!$isPublished) {
                $this->publishes([
                  __DIR__ . '/../database/migrations/pausable_job_migration.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_pausable_job_migration.php'),
                ], 'migrations');
            }

        }
    }

    /**
     * Register the pausable database queue connector.
     *
     * @param  \Illuminate\Queue\QueueManager  $manager
     * @return void
     */
    protected function registerDatabaseConnector($manager)
    {
        /**
         * *Overriding the default `DatabaseConnector` with `PausableDatabaseConnector`
         */
        $manager->addConnector('database', function () {
            return new PausableDatabaseConnector($this->app['db']);
        });
    }
}
