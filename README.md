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


## Installation

### Composer

composer require corbinjurgens/qstorage

### Manual Installation

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

## Basics

Basically it can be used exactly as the usual laravel Storage system. However, all functions no longer take a path parameter.

For example `Storage::get('folder/file.txt')` becomes `QStorage::file('folder/file.txt')->get()`

Using 'file' sets the path. You can also use 'folder' to set path when it is a folder.
These will open a new instance relative to the current instance and then set the new path. For example

```
$disk = QStorage::folder('folder');// 'folder', path is unaffected by the following lines
$file = $disk->file('file.txt');// 'folder/file.txt'
$file1 = $disk->file('file2.txt');// 'folder/file2.txt'
```

> Under the hood this makes use of the 'setSub' function. By opening file or folder with a path already set, it uses the current path as a prefix and builds off that.

If you need to access the underlying disk instance, you can use 'getDisk', eg `QStorage::disk('data')->getDisk()->...`

## Extended Features

The 'move' and 'copy' functions can take a destination path as normal, or a QStorage instance. If the disk is different, it will automatically stream the file between the different disks for you.

```
QStorage::disk('local')->file('test.txt')->copy(QStorage::disk('s3')->file('test.txt'));
```

The files() and directories() functions now return a list of new QStorage instances, meaning you can chain more storage functions directly onto them. You can also use items() for both files and directories. Whether or not the results are directories is checked and set

```
$files = QStorage::folder('folder')->files();
foreach($files as $file){
	$contents = $file->get();
	...
	$file->put($contents);
}
```


## Added Features

A few functions have been added that are not available in the base Laravel Filesystem

You may zip an entire directory by the following function:

```
$destination = QStorage::disk('s3')->file('archive.zip');
QStorage::disk('local')->folder('test')->zip($destination);
```

It does not matter if the disks are different, it will use a shared local space to zip the file. The default location is a local driver at "/tmp/process". You can change this by setting `QStorage::$operation_disk_fetcher` as a closure in your app service provider':

```
QStorage::$operation_disk_fetcher = function(){
  return \Storage::disk('custom');
};
```

In addition to the original 'path' function you can also use: relativePath (path relative to the disk) and leafPath (path in the current 'setSub' context ie. within current folder etc). 'path' also has the alias 'absolutePath'

# Changelog

- 2.0.0
  - The 'path' function used to set the current path is changed to 'setPath', and so 'path' can be used in the original Laravel Storage way to fetch the absolute path
  - Added Zip function
  - Cross-disk move and copy
  - Emphasis on file vs directory, can use 'isDir' to check if the retrieved items is a directory
- 1.0.0 
  - Init

