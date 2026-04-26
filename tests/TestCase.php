<?php

namespace Corbinjurgens\Q\Tests;

use Corbinjurgens\Q\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

	protected function getPackageProviders($app)
	{
		return [ServiceProvider::class];
	}

	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('cache.default', 'array');
		$app['config']->set('filesystems.default', 'local');
		$app['config']->set('filesystems.disks.local', [
			'driver' => 'local',
			'root' => sys_get_temp_dir() . '/q-tests-' . getmypid(),
		]);
		$app['config']->set('filesystems.disks.secondary', [
			'driver' => 'local',
			'root' => sys_get_temp_dir() . '/q-tests-secondary-' . getmypid(),
		]);
	}

	protected function tearDown(): void
	{
		foreach ([
			sys_get_temp_dir() . '/q-tests-' . getmypid(),
			sys_get_temp_dir() . '/q-tests-secondary-' . getmypid(),
		] as $root) {
			$this->removeTree($root);
		}
		parent::tearDown();
	}

	private function removeTree($dir)
	{
		if (!is_dir($dir)) {
			return;
		}
		foreach (scandir($dir) as $entry) {
			if ($entry === '.' || $entry === '..') {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $entry;
			is_dir($path) ? $this->removeTree($path) : @unlink($path);
		}
		@rmdir($dir);
	}
}
