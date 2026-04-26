<?php

namespace Corbinjurgens\Q\Concerns;

trait Tools
{

	public static function separator()
	{
		return static::SEPARATOR;
	}

	public static function filterSegment($segment)
	{
		return is_string($segment) && strlen($segment);
	}

	public static function trimSegment($segment)
	{
		return is_string($segment) ? trim($segment, static::separator()) : $segment;
	}

	public static function joinSegments(array $segments)
	{
		return join(static::separator(), array_filter(array_map([static::class, 'trimSegment'], $segments), [static::class, 'filterSegment']));
	}

	public static function calculateLeaf($path, $parent)
	{
		$length = strlen($parent);
		return substr($path, $length ? ($length + 1) : 0);
	}

	public static function walkSegments(array $segments)
	{
		$result = [];
		foreach ($segments as $segment) {
			if (!is_string($segment)) {
				continue;
			}
			foreach (explode(static::separator(), $segment) as $bit) {
				if ($bit === '' || $bit === '.') {
					continue;
				}
				if ($bit === '..') {
					if (empty($result)) {
						throw new \Exception("You can't go back any more");
					}
					array_pop($result);
					continue;
				}
				$result[] = $bit;
			}
		}
		return join(static::separator(), $result);
	}
}
