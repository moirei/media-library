# Media Storage

Media storage allow uploaded files to be collected in domain or workspace specific categories. This can be particularly use for multi-tenant and multi-domain applications where storage limits, disk, locations, etc. maybe dependent on the authenticated user or the request context.

For ease of use, you may configure preset storages accessible by name.

```php
$storage = Storage::get('app');
```

Where `app` is preconfigured and created in database if it does not exist.

## Active storage

You can easily switch between storages with the `use` method.
Any storage of choice can be set in use at any point, including in your app middleware.

```php
Storage::use('app');

// or
Storage::use($storageId);

// or
$storage = Storage::get($storageId);
Storage::use($storage);

// or
Storage::createAndUse([
  'name' => 'Products',
  'disk' => 's3',
  ...
]);
```

Once in use, file uploads and folder creation are by default stored in the active storage.

## Get active

In-use storages can be retrieved using the `active` method.

```php
$storage = Storage::active();
```
