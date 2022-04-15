<?php

namespace Corbinjurgens\QStorage;

use Storage;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
	
	static $name = 'qstorage';
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
      $this->mergeConfigFrom(__DIR__.'/config/qstorage.php', 'qstorage');
		  $this->app->bind(self::$name, QStorage::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
      $this->publishes([
        __DIR__.'/config/qstorage.php' => config_path('qstorage.php'),
      ], self::$name. '-config');
    }
}
