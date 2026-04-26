<?php

namespace Corbinjurgens\Q\Facades;

use Illuminate\Support\Facades\Facade;

class QCache extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'qcache';
	}
}
