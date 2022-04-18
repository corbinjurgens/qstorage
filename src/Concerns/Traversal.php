<?php

namespace Corbinjurgens\QStorage\Concerns;

trait Traversal
{

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

	public function operationPath(){
		return static::joinPaths([($this->as ?? $this->path)]);
	}

	public function open($path){
		$sub = $this->relativePath();
		return $this->clone()->sub($sub)->path($path);
	}

	public function folder($path){
		return $this->open($path);
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
		return static::$operation_disk = isset(static::$operation_disk_fetcher) ? (static::$operation_disk_fetcher)() : static::operationDiskBuild();
	}

	protected static function operationDiskBuild(){
		return app('filesystem')->createLocalDriver([
			'driver' => 'local',
			'root' => 'tmp',
			'visibility' => 'public'
		]);
	}
}
