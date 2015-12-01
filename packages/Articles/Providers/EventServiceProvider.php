<?php

namespace PhpSoft\Articles\Providers;

use PhpSoft\Articles\Models\Category;
use \Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Str;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        Category::saving(function($category)
        {
            if (empty($category->alias)) {
                $category->alias = Str::slug($category->name)
                    .'-'.Uuid::generate(4);
            }

            return true;
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        //
    }
}
