# Moving Files & Folders

## Move Files

Files can be moved to any location in a storage. When moving files, all necessary folders are created.

```php
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\Folder;
...

$storage = MediaStorage::active();
$storage->move($file, 'products/chargers');
```

A folder instance can also be provided directly.

```php
...
$folder = $storage->resolveFolder('products/chargers');
$storage->move($file, $folder);
```

## Move Folders

Just like files, folders can be moved to any location in a storage along with their files.

```php
$folder = $storage->resolveFolder('products/chargers');
$storage->moveFolder($folder, 'products/Images');
```

A folder instance can also be provided as the destination argument.
