# Creating Folders

The below snippet creates a Folder named `"Chargers"` located in `accessories`. All necessary folders, in this case a root folders named `"accessories"`, is also created.

```php
$storage = MediaStorage::active();

$folder = $storage->createFolder([
  'name' => 'Chargers',
  'location' => 'accessories',
  'description' => ...,
  'private' => true,
]);

// Or
$folder = $storage->assertFolder('accessories/Chargers');
```

By default folders' privacy are set to that of the storage.

## Retrieving folders

Folders can be resolved from their storage using the `resolveFolder` method if the `ID` is unknown.

```php
// Get folder if it exists
$folder = $storage->resolveFolder('accessories/Chargers');
```

If `ID` is known, you may use the model class directly

```php
$folder = Folder::find($folderId);
```
