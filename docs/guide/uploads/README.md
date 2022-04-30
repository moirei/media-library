# File Uploads

File and attachment uploads are done using the `MOIREI\MediaLibrary\Upload` class. Uploadable files must of be type `Illuminate\Http\UploadedFile`.

```php
$uploadedFile = $request->file('file');

$file = (new Upload($uploadedFile))->save();
```

A new upload can be created using the `make` method

```php
$file = Upload::make($uploadedFile)
          ->name('Birds image') // provide a presentment-friendment file name
          ->description('An example image of birds')
          ->private(true)
          ->save();
```

If you want to create and save an upload without making further changes to the instance, use the `uploadFile` method to create and save in one go.

```php
$file = Upload::uploadFile($uploadedFile);
```

## Saving from URLs

External files can be sourced and saved with ease.

```php
$file = Upload::fromUrl('https://cloudimage.io/../birds.jpg')
        ->location('external')
        ->save();
```

## Provide storage location

When an upload is created, you can set a location of your choice using the `location` or `folder` methods.

> For file uploads only

```php
$attachment = Upload::make($uploadedFile)
        ->location('Images/Products')
        ->save();
```

Or provide a folder

```php
$file = Upload::make($uploadedFile)
        ->folder('Images/Products')
        ->save();
// or
$file = Upload::make($uploadedFile)
        ->folder(Folder::find(...))
        ->save();
```

Saving to non-root locations will create appropriate folders as necessary.

## Upload storage

By default, the uploader uses the _active_ media storage to persist files. The `storage` method may be used to provide an alternative storage.

> For files only

```php
$file = Upload::make($uploadedFile)
        ->storage(MediaStorage::get(...))
        ->save();
```

## Max upload size

By default, the upload limits set in your configs is used to validate uploaded files and attachments. The follow with provide a temporal limit for the created upload.

```php
$file = Upload::make($uploadedFile)
        ->maxSize(1 * 1024 * 1024) // 1MB
        ->save();
```

## Upload types

Similar to `maxSize`, methods `allow` and `mime` allwo to temporarily define allowed uploadable types. The following with allow `png` images and will throw an error for all other file types.

```php
$file = Upload::make($uploadedFile)
        ->allow('png')
        ->save();
```

## Image manipulation

Uploaded image files can be resized, crop, converted to other formats or even filtered before they're saved.

```php
$file = Upload::make($uploadedFile)
        ->resize(400, 400)
        ->convertTo('webp')
        ->apply([
          \Intervention\Image\Filters\DemoFilter::class,
          \App\Filters\GreyLangFilter::class => [30, 2], // with constructor args
        ])
        ->save();
```

See [Intervention Image filters](https://image.intervention.io/v2/usage/filters) for how to create custom filters.

## Model-owned files

Uploaded files are by default available app-wide for any model. Uploaded files can be saved as private to a model instance.

```php
$file = Upload::make($uploadedFile)
        ->for(Product::find(1))
        ->save();
```

Model-owned files do not appear in _browse_ results by default.

## File metadata

Arbitrary metadata can be added to filed files using the `withMeta` method. Accepts both arrays and Collections.

> For files only

```php
$file = Upload::make($uploadedFile)
        ->withMeta([
          'key-1' => 1,
        ])
        ->withMeta(collect([
          'key-2' => 2,
        ]))
        ->save();
```
