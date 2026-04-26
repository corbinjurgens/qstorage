<?php

namespace Corbinjurgens\Q;

use Illuminate\Support\Facades\Storage;

class QStorage
{

	use Concerns\Tools;
	use Concerns\Storage\Files;
	use Concerns\Storage\Traversal;

	const SEPARATOR = '/';

	protected $disk;

	public function __construct($disk = null)
	{
		$this->setDisk(is_object($disk) ? $disk : Storage::disk($disk));
	}

	public function clone()
	{
		return clone $this;
	}

	public function setDisk($disk)
	{
		$this->disk = $disk;
		return $this;
	}

	public function getDisk()
	{
		return $this->disk;
	}

	public static function disk($disk = null)
	{
		return new static($disk);
	}

	public function __call($name, $arguments)
	{
		if (in_array($name, (array) config('qstorage.passthrough', []))) {
			return $this->getDisk()->$name(...$arguments);
		}

		array_unshift($arguments, $this->relativePath());
		return $this->getDisk()->$name(...$arguments);
	}
}
