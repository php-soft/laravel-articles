<?php

namespace PhpSoft\Articles\Providers;

use Illuminate\Support\ServiceProvider;
use PhpSoft\Articles\Commands\MigrationCommand;

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

        // Publish config files
        $this->publishes([
            __DIR__.'/../config/phpsoft.article.php' => config_path('phpsoft.article.php'),
        ]);

        // Publish migration files
        $this->publishes([
            __DIR__.'/../Commands/migrations' => base_path('database/migrations'),
        ], 'migrations');
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/phpsoft.article.php',
            'phpsoft.article'
        );

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
