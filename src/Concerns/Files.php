<?php

namespace Corbinjurgens\QStorage\Concerns;

trait Files
{

	public function hydrateFiles($paths){
		$base = $this->clone();
		$path = $this->relativePath();
		$base->sub($path);

		$depth = strlen($path) ? count(explode(DIRECTORY_SEPARATOR, $path)) : 0;
		return array_map(function($path) use ($base, $depth){
			$path_parts = explode(DIRECTORY_SEPARATOR, $path);
			$leaf_parts = array_slice($path_parts, $depth);
			return $base->clone()->path(static::joinPaths($leaf_parts));
		}, $paths);
	}
}
