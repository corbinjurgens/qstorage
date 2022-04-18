<?php

namespace Corbinjurgens\QStorage\Concerns;

use Corbinjurgens\QStorage\QStorage;

trait Files
{

	public function hydrateFiles($paths){
		$parent = $this;
		$base = $this->clone();
		$path = $this->relativePath();
		$base->sub($path);

		$depth = strlen($path) ? count(explode(DIRECTORY_SEPARATOR, $path)) : 0;
		$files = array_map(function($path) use ($parent, $base, $depth){
			$path_parts = explode(DIRECTORY_SEPARATOR, $path);
			$leaf_parts = array_slice($path_parts, $depth);
			return $base->clone()->path(static::joinPaths($leaf_parts))->setParent($parent);
		}, $paths);
		$this->setChildren($files);
		return $files;
	}

	/** @var \Corbinjurgens\QStorage\QStorage[] */
	protected $children = [];

	public function setChildren(array $files){
		$this->children = $files;
		return $this;
	}

	public function getChildren(){
		return $this->children;
	}

	public function clearChildren(){
		return $this->setChildren([]);
	}

	public function appendChild(QStorage $file){
		$this->children[] = $file;
	}

	public function appendChildren(array $files){
		$this->children = array_merge($this->children, $files);
	}

	protected $parent;

	public function setParent(QStorage $file){
		$this->parent = $file;
		return $this;
	}

	public function getParent(){
		return $this->parent;
	}

	/**
	 * Copy all children of the current instance (or passed directly) to the given destination
	 */
	public function copyAll(QStorage $destination, array $files = null){
		$this->doAll(function($file, $destination){
			$destination->open($file->operationPath())->writeStream($file->readStream());
		}, $destination, $files);
	}

	protected function doAll(\Closure $do, QStorage $destination, array $files = null){
		$files = $files ?? $this->getChildren();
		foreach($files as $file){
			$do($file, $destination);
		}
	}

}
