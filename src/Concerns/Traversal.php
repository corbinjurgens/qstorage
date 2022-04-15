<?php

namespace Corbinjurgens\QStorage\Concerns;

trait Traversal
{

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
}
