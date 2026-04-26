# Q

Object-oriented, identifier-bound wrappers around Laravel's `Storage` and `Cache` facades.

If repeating the same path or key on every call feels noisy, **Q** binds the identifier to an object so the path/key travels with it:

```php
// Storage — path travels with the object
$file = QStorage::disk('data')->file('reports/2026.csv');
if ($file->exists()) {
    $contents = $file->get();
    // ...
    $file->put($contents);
}

// Cache — key travels with the object
$counter = QCache::store('redis')->key('jobs:processed');
$counter->put(0, 3600);
$counter->increment();
$counter->get();
```

Anything not implemented directly is forwarded to the underlying disk/repository with the bound path/key auto-prepended, so the full Laravel API is still available.

## Requirements

- PHP 7.2 or higher
- Laravel 5.5+ — CI tests Laravel 6 through 12; older versions are supported via stable Storage/Cache APIs but not exercised by the matrix.

## Installation

```
composer require corbinjurgens/q
```

The service provider and facade aliases (`QStorage`, `QCache`) are registered via package discovery.

To publish either config file:

```
php artisan vendor:publish --tag=qstorage-config
php artisan vendor:publish --tag=qcache-config
```

## QStorage

### Basics

`QStorage` works exactly like Laravel's `Storage` facade, except path arguments are bound to the instance instead of being passed every call:

```php
// Before
Storage::disk('data')->get('folder/file.txt');

// After
QStorage::disk('data')->file('folder/file.txt')->get();
```

`file()` and `folder()` open a new instance relative to the current one:

```php
$disk = QStorage::folder('reports');             // bound to "reports/"
$jan  = $disk->file('2026-01.csv');              // "reports/2026-01.csv"
$feb  = $disk->file('2026-02.csv');              // "reports/2026-02.csv"
```

The underlying disk is available via `getDisk()`:

```php
QStorage::disk('s3')->getDisk()->temporaryUrl(...);
```

### Listing

`files()`, `directories()`, `allFiles()`, `allDirectories()`, `items()`, and `allItems()` return arrays of `QStorage` instances, so you can chain operations directly:

```php
foreach (QStorage::folder('inbox')->files() as $file) {
    $file->move('archive/' . $file->leafPath());
}
```

`isDir()` reports whether an item is a directory.

### Traversal

`cd()` and `ls()` give shell-like navigation:

```php
$root = QStorage::disk('local');
$root->cd('reports/2026')->ls();           // list files
$root->cd('/absolute/from/disk/root');     // leading slash resets to disk root
```

### Move and copy

`move()` and `copy()` accept a string path or another `QStorage` instance on the same disk:

```php
QStorage::disk('data')
    ->file('temp/draft.txt')
    ->copy(QStorage::disk('data')->file('archive/draft.txt'));
```

For cross-disk transfers, use Laravel's native streams:

```php
$dst->writeStream($src->readStream());
```

### Path helpers

- `path()` / `absolutePath()` — full filesystem path (delegates to the disk)
- `relativePath()` — path relative to the disk root
- `leafPath()` — path within the current `setSub()` context

### Pass-through methods

Any method called on `QStorage` that isn't defined directly is forwarded to the underlying disk with the bound path inserted as the first argument. To call a method without that injection, list it in `config/qstorage.php`:

```php
'passthrough' => ['forgetDisk', 'extend'],
```

## QCache

`QCache` mirrors the same idea against Laravel's cache repository. The key travels with the object:

```php
// Before
Cache::put('user:1:profile', $data, 3600);
$data = Cache::get('user:1:profile');

// After
$profile = QCache::key('user:1:profile');
$profile->put($data, 3600);
$data = $profile->get();
```

Pick a store with `store()`:

```php
QCache::store('redis')->key('queue:depth')->get();
```

### Prefixes

`prefix()` namespaces a group of keys, like `folder()` for storage:

```php
$user = QCache::store('redis')->prefix('user:1');
$user->key('name')->put('Alice', 3600);          // "user:1:name"
$user->key('email')->put('a@x.io', 3600);        // "user:1:email"
```

`fullKey()` returns the joined `prefix:key`. `leafKey()` returns just the bound leaf.

### Tags

`tags()` returns a clone bound to a tagged repository (only on stores that support tagging, e.g. redis, memcached):

```php
QCache::store('redis')->tags(['users'])->key('1:profile')->put($data, 3600);
QCache::store('redis')->tags(['users'])->flush();
```

### Pass-through methods

Methods like `flush()`, `many()`, `putMany()`, and `setEventDispatcher()` operate at the store level rather than on a single key, so they are forwarded directly without the bound key. The list lives in `config/qcache.php` and can be customised.

## When to reach for something else

Q is intentionally a thin ergonomic layer. For features outside that scope, use the right tool:

- **Cross-disk zipping or large archives** — [`stechstudio/laravel-zipstream`](https://github.com/stechstudio/laravel-zipstream) streams archives across drivers efficiently.
- **Cross-disk file transfer** — Laravel's native `writeStream()` / `readStream()` already handles this.
- **Symfony users** — [`zenstruck/filesystem`](https://github.com/zenstruck/filesystem) covers similar ground.

## License

MIT — see [LICENSE](LICENSE).
