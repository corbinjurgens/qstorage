# Changelog

All notable changes to this package are documented here.

## 3.0.0 — 2026-04-26

Major rewrite. The package has been renamed from `corbinjurgens/qstorage` to `corbinjurgens/q` and now wraps both `Storage` and `Cache`.

### Added
- `QCache` — object-oriented, key-bound wrapper around Laravel's cache repository, mirroring `QStorage`.
- `QCache::key()`, `QCache::prefix()`, `QCache::tags()` for fluent key binding and namespacing.
- Test suite using Orchestra Testbench.
- GitHub Actions CI matrix across PHP 7.2–8.3 and Laravel 5.5–12.

### Changed
- Namespace changed from `Corbinjurgens\QStorage` to `Corbinjurgens\Q`.
- Facade aliases stay `QStorage` and `QCache` but now resolve from `Corbinjurgens\Q\Facades\*`.
- Path separator for storage normalised to `/` (Flysystem convention) instead of `DIRECTORY_SEPARATOR`.
- Storage utility helpers renamed (`joinPaths` → `joinSegments`, `walkPaths` → `walkSegments`, etc.) and made separator-agnostic via a host-class `SEPARATOR` constant.

### Removed
- `QStorage::zip()` — replaced by recommending [`stechstudio/laravel-zipstream`](https://github.com/stechstudio/laravel-zipstream), which streams archives across disks more efficiently.
- `QStorage::crossDiskCopy()` — superseded by Laravel's native `writeStream()` / `readStream()` (available since Laravel 5.7). `move()` / `copy()` now throw a clear exception when given a cross-disk target so users are pointed at the right API.
- The `operationDisk` machinery that only existed for `zip()`.

### Migration from 2.x

```php
// Old
use Corbinjurgens\QStorage\QStorage;
use Corbinjurgens\QStorage\Facade as QStorage;

// New
use Corbinjurgens\Q\QStorage;
use Corbinjurgens\Q\Facades\QStorage;
```

For cross-disk copies that previously used `crossDiskCopy()`:

```php
// Old
$src->crossDiskCopy($dst);

// New
$dst->writeStream($src->readStream());
```

For zipping directories that previously used `zip()`, install `stechstudio/laravel-zipstream` and use its `Zip::create(...)->addFromDisk(...)->saveToDisk(...)` API.

## 2.0.0
- The `path()` function used to set the current path was renamed to `setPath()`, freeing `path()` to return the absolute path matching Laravel Storage.
- Added `zip()`.
- Added cross-disk move/copy.
- Distinguished file vs directory; added `isDir()`.

## 1.0.0
- Initial release.
