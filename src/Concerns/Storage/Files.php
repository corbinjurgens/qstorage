<?php

namespace Corbinjurgens\Q\Concerns\Storage;

use Corbinjurgens\Q\QStorage;

trait Files
{

	/**
	 * Wrap an array of disk-relative paths into QStorage instances bound to the
	 * current sub-path context.
	 *
	 * @param  string[]  $paths
	 * @return \Corbinjurgens\Q\QStorage[]
	 */
	public function hydratePaths(array $paths, $isDir, $children = false)
	{
		$base = $this->clone();
		$parent = $this->relativePath();
		$base->setSub($parent);
		$base->setParent($this);

		$files = array_map(function ($path) use ($base, $parent, $isDir) {
			return $base->clone()
				->setPath(static::calculateLeaf($path, $parent))
				->setDir($isDir);
		}, $paths);

		if ($children === true) {
			$this->setChildren($files);
		}
		return $files;
	}

	public function files($recursive = false)
	{
		$method = $recursive ? 'allFiles' : 'files';
		$paths = (array) $this->getDisk()->$method($this->relativePath());
		return $this->hydratePaths($paths, false);
	}

	public function allFiles()
	{
		return $this->files(true);
	}

	public function directories($recursive = false)
	{
		$method = $recursive ? 'allDirectories' : 'directories';
		$paths = (array) $this->getDisk()->$method($this->relativePath());
		return $this->hydratePaths($paths, true);
	}

	public function allDirectories()
	{
		return $this->directories(true);
	}

	public function items($recursive = false)
	{
		return array_merge($this->directories($recursive), $this->files($recursive));
	}

	public function allItems()
	{
		return $this->items(true);
	}

	/** @var \Corbinjurgens\Q\QStorage[] */
	protected $children = [];

	public function setChildren(array $files)
	{
		$this->children = $files;
		return $this;
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function clearChildren()
	{
		return $this->setChildren([]);
	}

	public function appendChild(QStorage $file)
	{
		$this->children[] = $file;
	}

	public function appendChildren(array $files)
	{
		$this->children = array_merge($this->children, $files);
	}

	protected $parent;

	public function setParent(QStorage $file)
	{
		$this->parent = $file;
		return $this;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function move($to)
	{
		$path = $this->relativePath();
		if (!$to instanceof QStorage) {
			return $this->getDisk()->move($path, $to);
		}
		if ($this->getDisk() !== $to->getDisk()) {
			throw new \Exception('move() targets must share the same disk. For cross-disk transfers stream the file: $dst->writeStream($src->readStream()).');
		}
		return $this->getDisk()->move($path, $to->relativePath());
	}

	public function copy($to)
	{
		$path = $this->relativePath();
		if (!$to instanceof QStorage) {
			return $this->getDisk()->copy($path, $to);
		}
		if ($this->getDisk() !== $to->getDisk()) {
			throw new \Exception('copy() targets must share the same disk. For cross-disk transfers stream the file: $dst->writeStream($src->readStream()).');
		}
		return $this->getDisk()->copy($path, $to->relativePath());
	}
}
