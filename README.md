# Laravel Media Library

Laravel application media content management made easy.



## Documentation

All documentation is available at [the documentation site](https://moirei.github.io/media-library).



## :green_heart: Features

* **Image URL manipulation**: manipulate uploaded images with Cloud Image compatible API for your responsive frontend
* **Eloquent models**: associate files with Eloquent models. Attach different file types (video, images) to different model attributes with polymorphic many-to-many while having the ability to fully configure model-specific uploads.
* **Secure**: public, signed, and protected endpoints and file sharing and downloads regardless of storage disk. Includes configurable middleware per API endpoints for fine-grained permissions and authorization
* **File sharing**: securely share files a directories with anyone. Including none registered users.
* **Uploads**: upload by external URL, local path, File or request UploadFile. The package exposes internal API endpoints for uploads, downloads, streams and other admin operations
* **Control**: control uploads and shared files by types, size, size per type
* **Responsive images**: automatically resize images on uploads for responsive frontend
* **Storage system**: provides media storage namespacing for multi-tenant and multi-domain uploads with varying configurations.
* **Rich text attachments**: seamlessly management attachments and integrate model text fields




```php
...
use MOIREI\MediaLibrary\Casts\AsMediaItem;
use MOIREI\MediaLibrary\Casts\AsMediaItems;
use MOIREI\MediaLibrary\Traits\InteractsWithMedia;

class Product extends Model
{
   use InteractsWithMedia;

   $casts = [
      'image' => AsMediaItem::class,
      'gallery' => AsMediaItems::class,
   ];
   ...
}
...

$video = File::find('video-file-id');

$product = Product::create([
   'name' => 'MOIREI MP202+',
   'image' => 'image-file-id1',
   'gallery' => ['image-file-id2', $video],
]);
```



## Installation

```bash
composer require moirei/media-library
```



### Publish the config

```php
php artisan vendor:publish --tag=media-library-config
```



### Prepare the database

```php
php artisan vendor:publish --tag=media-library-migrations
```

Then run the migrations

```bash
php artisan migrate
```



## Changelog

Please see [CHANGELOG](./CHANGELOG.md).



## Credits

- This package was inspired by [classic-o/nova-media-library](https://github.com/classic-o/nova-media-library), an excellent package for [Laravel Nova](https://nova.laravel.com/)
- [Augustus Okoye](https://github.com/augustusnaz)



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.