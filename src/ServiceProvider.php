<?php

namespace Corbinjurgens\Q;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/config/qstorage.php', 'qstorage');
		$this->mergeConfigFrom(__DIR__ . '/config/qcache.php', 'qcache');

		$this->app->bind('qstorage', QStorage::class);
		$this->app->bind('qcache', QCache::class);
	}

	public function boot()
	{
		$this->publishes([
			__DIR__ . '/config/qstorage.php' => config_path('qstorage.php'),
		], 'qstorage-config');

		$this->publishes([
			__DIR__ . '/config/qcache.php' => config_path('qcache.php'),
		], 'qcache-config');
	}
}
