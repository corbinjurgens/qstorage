<?php

namespace Corbinjurgens\Q\Facades;

use Illuminate\Support\Facades\Facade;

class QStorage extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'qstorage';
	}
}
