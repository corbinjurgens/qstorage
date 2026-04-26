<?php

namespace Corbinjurgens\Q\Tests;

use Corbinjurgens\Q\QCache;

class QCacheTest extends TestCase
{

	public function test_put_and_get_round_trip()
	{
		$item = QCache::store('array')->key('user:1:name');
		$item->put('Alice', 60);

		$this->assertTrue($item->has());
		$this->assertSame('Alice', $item->get());
	}

	public function test_key_is_bound_to_the_instance()
	{
		$user = QCache::store('array')->prefix('user:1');

		$name = $user->key('name');
		$email = $user->key('email');

		$name->put('Alice', 60);
		$email->put('alice@example.com', 60);

		$this->assertSame('user:1:name', $name->fullKey());
		$this->assertSame('user:1:email', $email->fullKey());
		$this->assertSame('Alice', $name->get());
		$this->assertSame('alice@example.com', $email->get());
	}

	public function test_prefix_chains_extend_the_namespace()
	{
		$item = QCache::store('array')
			->prefix('app')
			->prefix('users')
			->key('1');

		$this->assertSame('app:users:1', $item->fullKey());

		$item->put('value', 60);
		$this->assertSame('value', $item->get());
	}

	public function test_increment_and_decrement_use_bound_key()
	{
		$counter = QCache::store('array')->key('jobs:processed');
		$counter->put(0, 60);

		$counter->increment();
		$counter->increment(4);
		$this->assertSame(5, $counter->get());

		$counter->decrement(2);
		$this->assertSame(3, $counter->get());
	}

	public function test_forget_removes_only_the_bound_key()
	{
		$a = QCache::store('array')->key('a');
		$b = QCache::store('array')->key('b');
		$a->put(1, 60);
		$b->put(2, 60);

		$a->forget();

		$this->assertFalse($a->has());
		$this->assertSame(2, $b->get());
	}

	public function test_remember_uses_bound_key()
	{
		$item = QCache::store('array')->key('expensive');
		$value = $item->remember(60, function () {
			return 'computed';
		});

		$this->assertSame('computed', $value);
		$this->assertSame('computed', $item->get());
	}

	public function test_open_creates_independent_instance()
	{
		$base = QCache::store('array')->key('base');
		$leaf = $base->key('leaf');

		$this->assertSame('base', $base->fullKey());
		$this->assertSame('base:leaf', $leaf->fullKey());
	}

	public function test_full_key_treats_empty_segments_as_no_op()
	{
		$item = QCache::store('array')->prefix('')->key('only');
		$this->assertSame('only', $item->fullKey());
	}
}
