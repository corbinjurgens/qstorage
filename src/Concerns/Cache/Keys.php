<?php

namespace Corbinjurgens\Q\Concerns\Cache;

trait Keys
{

	protected $prefix = '';

	public function setPrefix($prefix = '')
	{
		$this->prefix = static::trimSegment($prefix);
		return $this;
	}

	protected $key = '';

	public function setKey($key = '')
	{
		$this->key = static::trimSegment($key);
		return $this;
	}

	/**
	 * Fully qualified cache key (prefix joined with the bound key).
	 */
	public function fullKey()
	{
		return static::joinSegments([$this->prefix, $this->key]);
	}

	public function leafKey()
	{
		return $this->key;
	}

	/**
	 * Open a new instance bound to the given key, using the current fullKey() as the new prefix.
	 */
	public function open($key)
	{
		return $this->clone()->setPrefix($this->fullKey())->setKey($key);
	}

	public function key($key)
	{
		return $this->open($key);
	}

	/**
	 * Open a new instance with an extended prefix (no key bound).
	 */
	public function prefix($prefix)
	{
		return $this->clone()
			->setPrefix(static::joinSegments([$this->fullKey(), $prefix]))
			->setKey('');
	}

	/**
	 * Walk to a new prefix, shell-style. A leading separator (or no argument)
	 * resets to the root; '..' segments walk up one level. Operates on the
	 * prefix only — any bound key is dropped on the resulting instance.
	 */
	public function cd($prefix = null)
	{
		$separator = static::separator();
		if ($prefix === null || strpos($prefix, $separator) === 0) {
			$walked = static::walkSegments([$prefix === null ? '' : $prefix]);
		} else {
			$walked = static::walkSegments([$this->prefix, $prefix]);
		}
		return $this->clone()->setPrefix($walked)->setKey('');
	}
}
