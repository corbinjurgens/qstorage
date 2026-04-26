<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Pass-through methods
	|--------------------------------------------------------------------------
	|
	| Methods listed here are forwarded to the underlying cache repository
	| as-is, without injecting the bound key as the first argument. Use this
	| for store-level methods that don't operate on a single key.
	|
	*/
	'passthrough' => [
		'flush',
		'getStore',
		'setEventDispatcher',
		'setDefaultCacheTime',
		'getDefaultCacheTime',
		'many',
		'getMultiple',
		'putMany',
		'setMultiple',
		'deleteMultiple',
	],
];
