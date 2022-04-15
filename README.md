# Introduction

Allows you to use laravel Storage in a more object-oriented fashion. If you are like me, it feels dirty to be repeating the same paths for different operations.

Before:

```
if (Storage::disk('data')->exists('folder/file.txt')){
  $contents = Storage::disk('data')->get('folder/file.txt');
  ...
  Storage::disk('data')->put('folder/file.txt', $contents);
}
```

After:

```
$file = QStorage::disk('data')->file('folder/file.txt');
if ($file->exists()){
  $contents = $file->get();
  ...
  $file->put($contents);
}
```

## Manual Installation

Copy the following to main composer.(in the case that the package is added to packages/corbinjurgens/qform)
```
 "autoload": {
	"psr-4": {
		"Corbinjurgens\\QStorage\\": "packages/corbinjurgens/qstorage/src"
	},
},
```
and run 
```
composer dump-autoload
```


Add the following to config/app.php providers
```
Corbinjurgens\QStorage\ServiceProvider::class,
```
Add alias to config/app.php alias
```
"QStorage" => Corbinjurgens\QStorage\Facade::class,
```

# Usage

Basically it can be used exactly as the usual laravel Storage system. However, all functions no longer take a path parameter.

For example `Storage::get('folder/file.txt')` becomes `QStorage::path('folder/file.txt')->get()`

> As you may see here the usual 'path' function has been overridden to specify which path an instance is pointing to. The original path method is accessed by 'absolutePath'

Using 'path' sets the current instance, but you can also use 'open' to open a new instance relative to the current instance and then set the new path. For example

```
$disk = QStorage::path('folder');// 'folder', path is unaffected by the following lines
$file = $disk->open('file.txt');// 'folder/file.txt'
$file1 = $disk->open('file2.txt');// 'folder/file2.txt'
```

> Under the hood this makes use of the 'sub' feature. By opening file or folder with a path already set, it uses the current path as a prefix and builds off that. You can achive the same result by using `QStorage::sub('folder')->path('file.txt')`

The files() and directories() functions now return a list of new QStorage instances, meaning you can chain more storage functions onto them

```
$files = QStorage::path('folder')->files();
foreach($files as $file){
	$contents = $file->get();
	...
	$file->put($contents);
}
```

> The original 'path' function which returned the absolute file path has been replaced with three different functions: absolutePath (same behaviour as original path) relativepath (path relative to the disk) and leafPath (path in the current 'sub' context ie. within current folder etc)