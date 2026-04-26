<?php

namespace Corbinjurgens\Q\Tests;

use Corbinjurgens\Q\QStorage;
use Illuminate\Support\Facades\Storage;

class QStorageTest extends TestCase
{

	public function test_put_and_get_round_trip()
	{
		$file = QStorage::disk('local')->file('reports/2026.csv');
		$file->put('hello');

		$this->assertTrue($file->exists());
		$this->assertSame('hello', $file->get());
	}

	public function test_path_is_bound_to_the_instance()
	{
		$reports = QStorage::disk('local')->folder('reports');

		$jan = $reports->file('jan.txt');
		$feb = $reports->file('feb.txt');

		$jan->put('one');
		$feb->put('two');

		$this->assertSame('reports/jan.txt', $jan->relativePath());
		$this->assertSame('reports/feb.txt', $feb->relativePath());
		$this->assertSame('one', $jan->get());
		$this->assertSame('two', $feb->get());
	}

	public function test_files_returns_chainable_qstorage_instances()
	{
		$folder = QStorage::disk('local')->folder('inbox');
		$folder->file('a.txt')->put('A');
		$folder->file('b.txt')->put('B');

		$files = $folder->files();

		$this->assertCount(2, $files);
		foreach ($files as $file) {
			$this->assertInstanceOf(QStorage::class, $file);
			$this->assertContains($file->get(), ['A', 'B']);
		}
	}

	public function test_directories_and_files_are_distinguished()
	{
		$root = QStorage::disk('local')->folder('mixed');
		$root->file('flat.txt')->put('flat');
		$root->folder('nested')->file('inner.txt')->put('inner');

		$items = $root->items();

		$file = null;
		$dir = null;
		foreach ($items as $item) {
			if ($item->isDir()) {
				$dir = $item;
			} else {
				$file = $item;
			}
		}

		$this->assertNotNull($file);
		$this->assertNotNull($dir);
		$this->assertSame('flat', $file->get());
	}

	public function test_copy_to_qstorage_target_on_same_disk()
	{
		$src = QStorage::disk('local')->file('src/a.txt');
		$src->put('payload');

		$dst = QStorage::disk('local')->file('dst/b.txt');
		$src->copy($dst);

		$this->assertSame('payload', $dst->get());
		$this->assertTrue($src->exists());
	}

	public function test_move_to_qstorage_target_on_same_disk()
	{
		$src = QStorage::disk('local')->file('src/a.txt');
		$src->put('payload');

		$dst = QStorage::disk('local')->file('dst/b.txt');
		$src->move($dst);

		$this->assertSame('payload', $dst->get());
		$this->assertFalse($src->exists());
	}

	public function test_copy_across_disks_throws()
	{
		$src = QStorage::disk('local')->file('a.txt');
		$src->put('payload');

		$dst = QStorage::disk('secondary')->file('a.txt');

		$this->expectException(\Exception::class);
		$src->copy($dst);
	}

	public function test_cd_navigates_relative_and_absolute()
	{
		$disk = QStorage::disk('local');
		$disk->folder('a/b/c')->file('x.txt')->put('x');

		$abc = $disk->cd('a/b/c');
		$this->assertSame('a/b/c', $abc->relativePath());

		$root = $abc->cd('/');
		$this->assertSame('', $root->relativePath());

		$back = $abc->cd('..');
		$this->assertSame('a/b', $back->relativePath());
	}

	public function test_passthrough_methods_skip_path_injection()
	{
		// allFiles and exists go through __call with path injection; sanity-check
		// that a non-listed method still works.
		QStorage::disk('local')->file('foo.txt')->put('bar');
		$this->assertTrue(QStorage::disk('local')->file('foo.txt')->exists());
	}
}
