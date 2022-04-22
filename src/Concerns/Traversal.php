<?php

namespace Corbinjurgens\QStorage\Concerns;

trait Traversal
{

	protected $sub = '';

	public function setSub(string $path = ''){
		$this->sub = static::trimPath($path);
		return $this;
	}

	protected $path = '';

	public function setPath(string $path = ''){
		$this->path = static::trimPath($path);
		return $this;
	}

	protected $dir = true;

	public function setDir(bool $dir){
		$this->dir = $dir;
		return $this;
	}

	public function isDir(){
		return $this->dir;
	}

	public function relativePath(){
		return static::joinCleanPaths([$this->sub, $this->path]);
	}

	public function absolutePath(){
		return $this->path();
	}

	public function leafPath(){
		return $this->path;
	}

	public function operationPath(){
		return static::joinCleanPaths([($this->as ?? $this->path)]);
	}

	public function open($path, $directory = false){
		$sub = $this->relativePath();
		return $this->clone()->setSub($sub)->setPath($path)->setDir($directory);
	}

	public function folder($path){
		return $this->open($path, true);
	}

	public function file($path){
		return $this->open($path);
	}

	protected $as;

	/**
	 * For zipping and bulk move/copy, when you want to save it at a different path other than the current relativePath
	 */
	public function as(string $path = null){
		$this->as = static::trimPath($path);
		return $this;
	}

	/** @var \Closure */
	public static $operation_disk_fetcher;

	protected static $operation_disk;

	public static function operationDisk(){
		if (isset(static::$operation_disk)) return static::$operation_disk;
		return static::$operation_disk = new static(isset(static::$operation_disk_fetcher) ? (static::$operation_disk_fetcher)() : static::operationDiskBuild());
	}

	protected static function operationDiskBuild(){
		return app('filesystem')->createLocalDriver([
			'driver' => 'local',
			'root' => '/tmp/process'
		]);
	}
}
