<?php

namespace Corbinjurgens\QStorage;

use Storage;

class QStorage {

	use Concerns\Tools;
	use Concerns\Files;
	use Concerns\Traversal;
	
	public function __construct($disk = null){
		$this->setDisk(Storage::disk($disk));
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

	protected $sub = '';

	public function sub(string $path = ''){
		$this->sub = static::trimPath($path);
		return $this;
	}

	protected $path = '';

	public function path(string $path = ''){
		$this->path = static::trimPath($path);
		return $this;
	}

	public function relativePath(){
		return static::joinPaths([$this->sub, $this->path]);
	}

	public function absolutePath(){
		return $this->getDisk()->path($this->relativePath());
	}

	public function leafPath(){
		return $this->path;
	}

	public function __call($name, $arguments){
		// Functions configured to call noromally
		if (in_array($name, config('qstorage.passthrough'))) return $this->getDisk()->$name(...$arguments);

		$path = $this->relativePath();
		array_unshift($arguments, $path);
		$result = $this->getDisk()->$name(...$arguments);
		if (!in_array($name, ['files', 'allFiles', 'directories', 'allDirectories'])) return $result;
		return static::hydrateFiles($result);
	}

}