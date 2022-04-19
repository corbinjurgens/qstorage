<?php

namespace Corbinjurgens\QStorage;

use Storage;

class QStorage {

	use Concerns\Tools;
	use Concerns\Files;
	use Concerns\Traversal;
	
	public function __construct($disk = null){
		$this->setDisk(is_object($disk) ? $disk : Storage::disk($disk));
	}

	public function clone(){
		return clone $this;
	}

	protected $disk;

	public function setDisk($disk){
		$this->disk = $disk;
		return $this;
	}

	public function getDisk(){
		return $this->disk;
	}

	public static function disk($disk = null){
		return new static($disk);
	}

	public function __call($name, $arguments){
		// Functions configured to call noromally
		if (in_array($name, config('qstorage.passthrough'))) return $this->getDisk()->$name(...$arguments);

		$path = $this->relativePath();
		array_unshift($arguments, $path);
		return $this->getDisk()->$name(...$arguments);
	}

}