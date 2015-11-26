<?php

namespace PhpSoft\Comments\Providers;

use Illuminate\Support\ServiceProvider;
use PhpSoft\Comments\Commands\MigrationCommand;

class ArticleServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        // Set views path
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'phpsoft.articles');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/phpsoft.articles'),
        ]);

        // Register commands
        $this->commands('phpsoft.articles.command.migration');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCommands();
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->bindShared('phpsoft.articles.command.migration', function () {
            return new MigrationCommand();
        });
    }
}
