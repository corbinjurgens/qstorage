<?php

namespace Corbinjurgens\QStorage;

use Illuminate\Support\Facades\Facade as BaseFacade;

use Corbinjurgens\QStorage\ServiceProvider as S;

class Facade extends BaseFacade {
   protected static function getFacadeAccessor() { return S::$name; }
}