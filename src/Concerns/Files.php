<?php

namespace Corbinjurgens\QStorage\Concerns;

use Corbinjurgens\QStorage\QStorage;
use ZipArchive;

trait Files
{

	/**
	 * @return \Corbinjurgens\QStorage\QStorage[]
	 */
	public function hydrateFiles($contents, $children = false){
		$parent = $this;
		$base = $this->clone();
		$path = $this->relativePath();
		$base->sub($path);

		$depth = static::calculateDepth($path);
		$files = array_map(function($content) use ($parent, $base, $depth){
			return $base->clone()->path(static::calculateLeaf($content['path'], $depth))->setParent($parent)->isDir($content['type'] === 'dir');
		}, $contents);
		if ($children === true) $this->setChildren($files);
		return $files;
	}

	public function items(callable $filter = null, $recursive = false){
		$contents = $this->listContents($recursive);
		if (isset($filter)){
			$contents = array_filter($contents, $filter);
		}
		return $this->hydrateFiles($contents);
	}

	public function allItems(){
		return $this->items(null, true);
	}

	public function files($recursive = false){
		return $this->items(function($content){
			return $content['type'] == 'file';
		}, $recursive);
	}

	public function allFiles(){
		return $this->files(true);
	}

	public function directories($recursive = false){
		return $this->items(function($content){
			return $content['type'] == 'dir';
		}, $recursive);
	}

	public function allDirectories(){
		return $this->directories(true);
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
	public function copyDirectory(QStorage $destination, array $contents = null){
		$contents = $contents ?? $this->allItems();
		$this->doAll(function($file, $destination){
			$target = $destination->open($file->operationPath());
			$file->isDir() ? $target->makeDirectory() : $target->writeStream($file->readStream());
		}, $destination, $contents);
	}

	public function zip(QStorage $destination, array $contents = null){
		$process = static::operationDisk()->folder(time() . '_' .mt_rand());
		$copy = $process->folder('contents');
		$target = $process->file('archive.zip');
		$this->copyDirectory($copy, $contents);

		$contents = $copy->allItems();
		
		$files = [];
		$folders = [];
		foreach($contents as $content){
			if ($content->isDir()){
				$folders[] = $content;
			}else{
				$files[] = $content;
			}
		}
		
		$addeds = [];

		$zip = new ZipArchive;
		$zip->open($target->absolutePath(), ZipArchive::CREATE);
		foreach ($files as $file) {
			$zip->addFile($file->absolutePath(), $file->operationPath());
			$addeds[] = $file->operationPath();
		}

		foreach($folders as $folder){
			$compare = $folder->operationPath() . DIRECTORY_SEPARATOR;
			$has = false;
			foreach($addeds as $added){
				if (strpos($added, $compare) !== false){
					$has = true;
					break;
				}
			}
			if (!$has){
				$zip->addEmptyDir($folder->operationPath());
			}
		}

		$zip->close();

		$destination->writeStream($target->readStream());

		$process->deleteDirectory();

		return $destination;
	}

	protected function doAll(\Closure $do, QStorage $destination, array $files = null){
		$files = $files ?? $this->getChildren();
		foreach($files as $file){
			$do($file, $destination);
		}
	}

}
