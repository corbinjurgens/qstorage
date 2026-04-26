<?php

namespace Corbinjurgens\Q;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;

class QCache
{

	use Concerns\Tools;
	use Concerns\Cache\Keys;

	const SEPARATOR = ':';

	public static function separator()
	{
		return config('qcache.separator', static::SEPARATOR);
	}

	protected $repository;

	public function __construct($store = null)
	{
		$this->setRepository(is_object($store) ? $store : Cache::store($store));
	}

	public function clone()
	{
		return clone $this;
	}

	public function setRepository($repository)
	{
		$this->repository = $repository;
		return $this;
	}

	public function getRepository()
	{
		return $this->repository;
	}

	public static function store($store = null)
	{
		return new static($store);
	}

	/**
	 * Open a new instance bound to a tagged repository. Only supported by stores
	 * that implement tagging (e.g. redis, memcached).
	 */
	public function tags($names)
	{
		$clone = clone $this;
		$clone->repository = $this->repository->tags($names);
		return $clone;
	}

	/**
	 * List the immediate children of the current prefix as new QCache instances.
	 *
	 * Cache stores differ in whether they can enumerate keys, so this is a
	 * best-effort method that supports the array and redis stores. Other
	 * stores (file, memcached, database, dynamodb) throw a RuntimeException.
	 *
	 * @return \Corbinjurgens\Q\QCache[]
	 */
	public function ls()
	{
		$separator = static::separator();
		$prefix = $this->fullKey();
		$matchPrefix = $prefix === '' ? '' : $prefix . $separator;

		$keys = $this->enumerateKeys($matchPrefix);
		$base = $this->clone()->setPrefix($prefix)->setKey('');

		$leaves = [];
		foreach ($keys as $key) {
			if ($matchPrefix !== '' && strpos($key, $matchPrefix) !== 0) {
				continue;
			}
			$relative = $matchPrefix === '' ? $key : substr($key, strlen($matchPrefix));
			$cut = strpos($relative, $separator);
			$leaf = $cut === false ? $relative : substr($relative, 0, $cut);
			if ($leaf === '') {
				continue;
			}
			$leaves[$leaf] = true;
		}

		$children = [];
		foreach (array_keys($leaves) as $leaf) {
			$children[] = $base->key($leaf);
		}
		return $children;
	}

	protected function enumerateKeys($matchPrefix)
	{
		$store = $this->repository->getStore();
		$cachePrefix = method_exists($store, 'getPrefix') ? (string) $store->getPrefix() : '';

		if ($store instanceof ArrayStore) {
			$reflection = new \ReflectionClass($store);
			$property = $reflection->getProperty('storage');
			$property->setAccessible(true);
			return array_map(function ($key) use ($cachePrefix) {
				return $cachePrefix !== '' && strpos($key, $cachePrefix) === 0
					? substr($key, strlen($cachePrefix))
					: $key;
			}, array_keys($property->getValue($store)));
		}

		if ($store instanceof RedisStore) {
			$connection = $store->connection();
			$pattern = $cachePrefix . $matchPrefix . '*';
			$keys = [];
			$cursor = '0';
			do {
				$result = $connection->scan($cursor, ['match' => $pattern, 'count' => 100]);
				if ($result === false) {
					break;
				}
				list($cursor, $batch) = $result;
				foreach ((array) $batch as $key) {
					$keys[] = $cachePrefix !== '' && strpos($key, $cachePrefix) === 0
						? substr($key, strlen($cachePrefix))
						: $key;
				}
			} while ((int) $cursor !== 0);
			return $keys;
		}

		throw new \RuntimeException(sprintf(
			'ls() is not supported on %s. Supported stores: array, redis.',
			get_class($store)
		));
	}

	public function __call($name, $arguments)
	{
		if (in_array($name, (array) config('qcache.passthrough', []))) {
			return $this->repository->$name(...$arguments);
		}

		array_unshift($arguments, $this->fullKey());
		return $this->repository->$name(...$arguments);
	}
}
