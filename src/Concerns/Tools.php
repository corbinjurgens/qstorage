<?php

namespace Corbinjurgens\QStorage\Concerns;

trait Tools
{

	public static function filterPath($path){
		return is_string($path) && strlen($path);
	}

	public static function trimPath($path){
		return is_string($path) ? trim($path, DIRECTORY_SEPARATOR) : $path;
	}

	public static function joinPaths(array $paths){
		return static::joinCleanPaths(array_filter(array_map([static::class, 'trimPath'], $paths), [static::class, 'filterPath']));
	}

	public static function joinCleanPaths(array $paths){
		return join(DIRECTORY_SEPARATOR, array_filter($paths, [static::class, 'filterPath']));
	}

	public static function calculateDepth(string $path){
		return strlen($path) ? count(explode(DIRECTORY_SEPARATOR, $path)) : 0;
	}

	public static function calculateLeaf(string $path, string $parent){
		$length = strlen($parent);
		return substr($path, $length ? ($length + 1) : 0);
	}
}
