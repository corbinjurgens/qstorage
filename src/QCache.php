<?php

namespace Corbinjurgens\Q;

use Illuminate\Support\Facades\Cache;

class QCache
{

	use Concerns\Tools;
	use Concerns\Cache\Keys;

	const SEPARATOR = ':';

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

	public function __call($name, $arguments)
	{
		if (in_array($name, (array) config('qcache.passthrough', []))) {
			return $this->repository->$name(...$arguments);
		}

		array_unshift($arguments, $this->fullKey());
		return $this->repository->$name(...$arguments);
	}
}
