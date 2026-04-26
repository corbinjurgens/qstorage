<?php

namespace Corbinjurgens\Q\Concerns\Storage;

trait Traversal
{

	protected $sub = '';

	/**
	 * Set the current sub-path (folder context)
	 */
	public function setSub($path = '')
	{
		$this->sub = static::trimSegment($path);
		return $this;
	}

	protected $path = '';

	/**
	 * Set the path to a file or folder relative to the current sub-path
	 */
	public function setPath($path = '')
	{
		$this->path = static::trimSegment($path);
		return $this;
	}

	protected $dir = true;

	public function setDir($dir)
	{
		$this->dir = (bool) $dir;
		return $this;
	}

	public function isDir()
	{
		return $this->dir;
	}

	/**
	 * Path relative to the disk root
	 */
	public function relativePath()
	{
		return static::joinSegments([$this->sub, $this->path]);
	}

	public function absolutePath()
	{
		return $this->path();
	}

	public function leafPath()
	{
		return $this->path;
	}

	public function open($path, $directory = false)
	{
		$sub = $this->relativePath();
		return $this->clone()->setSub($sub)->setPath($path)->setDir($directory);
	}

	public function folder($path)
	{
		return $this->open($path, true);
	}

	public function file($path)
	{
		return $this->open($path);
	}

	/**
	 * Traverse like a shell. Pass a leading slash to return to the disk root.
	 */
	public function cd($path = '/')
	{
		if (!$this->isDir()) {
			throw new \Exception('You should only call cd from a folder');
		}
		if (strpos($path, static::SEPARATOR) === 0) {
			return $this->clone()->setSub(static::walkSegments([$path]))->setPath()->setDir(true);
		}
		return $this->clone()->setSub(static::walkSegments([$this->relativePath(), $path]))->setPath()->setDir(true);
	}

	public function ls($path = '')
	{
		if ($path !== '') {
			$relative = $this->relativePath();
			$walked = static::walkSegments([$relative, $path]);
			return $this->open($walked)->items();
		}
		return $this->items();
	}
}
