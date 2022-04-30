# Browse Files

Uploaded files and created folders can be browsed to provide clients insights into their media storage.
This is particularly used for frontend integration.

```php
$storage = MediaStorage::get(...);
$content = $storage->browse('images/Products');
```

The above example returns a Collection of folders and files located at `"images/Products"`.

## Including model-owned files

To only return model-owned files in results, set the `modelFiles` option to `true`.

```php
$content = $storage->browse('images/Products', [
  'modelFiles' => true,
]);
```

## Ignoring folders

To only return files and ignore folders, set the `filesOnly` option to `true`.

```php
$content = $storage->browse('images/Products', [
  'filesOnly' => true,
]);
```

## Specifying file types and mime

You can use the `type` and/or `mime` options to filter file results. These options will automatically ignore folders.

```php
$content = $storage->browse('images/Products', [
  'type' => 'image',
  'mime' => 'jpg',
]);
```

## Specifying privacy

By default both public and private files and folders are included in results. Assign a `bool` value to the `private` option to filter results based on privacy.

```php
$content = $storage->browse('images/Products', [
  'private' => false,
]);
```
