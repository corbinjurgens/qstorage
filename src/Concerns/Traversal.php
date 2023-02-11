<?php

namespace Corbinjurgens\QStorage\Concerns;

trait Traversal
{

	protected $sub = '';

	/**
	 * Set current context
	 */
	public function setSub(string $path = ''){
		$this->sub = static::trimPath($path);
		return $this;
	}

	protected $path = '';

	/**
	 * Set path to a file or folder from the current context
	 */
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

	/**
	 * Relative path of the disk
	 */
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

	/**
	 * Traverse like linux
	 * 
	 * Use a slash at the start to return to top of the disk
	 */
	public function cd($path = '/'){
		if (!$this->isDir()) throw new \Exception('You should only call cd from a folder');
		if (strpos($path, '/') === 0){
			$result = $this->clone()->setSub(static::walkPaths([$path]))->setPath()->setDir(true);
		}else{
			$result = $this->clone()->setSub(static::walkPaths([$this->relativePath(), $path]))->setPath()->setDir(true);
		}
		return $result;
	}

	/**
	 * To match with cd
	 */
	public function ls(string $path = ''){
		if($path !== ''){
			$relative = $this->relativePath();
			$path = static::walkPaths([$relative, $path]);
			return $this->open($path)->items();
		}else{
			return $this->items();
		}
		
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
