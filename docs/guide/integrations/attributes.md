# Attributes

A number of casts are provided to help interface media files to Collections or an actual File instance. Casts also allow you to use media files at attribute level rather than associating them to your entire model. The stored value in your database column is the file ID or an array of IDs.

Ascribing the `MOIREI\MediaLibrary\Traits\InteractsWithMedia` trait will automatically handle associating or detaching both shared and model-owned files.

**Batteries-included Casts**

| Cast                                     | Description                                                                                                                                                            |
| ---------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `MOIREI\MediaLibrary\Casts\AsMediaFile`  | Provided for linking a single shared file to a model. Casts attributes to a [File](/data#file) instance.                                                               |
| `MOIREI\MediaLibrary\Casts\AsMediaFiles` | Use when linking multiple shared files to a model. Casts attributes to a custom Files Collection with ability to directly add and remove uploads                       |
| `MOIREI\MediaLibrary\Casts\AsMediaItem`  | Uploaded file is owned by the model. Casts to `MediaItemAttribute` with Arrayable access to the underlying File and additional utility methods for uploads and deletes |
| `MOIREI\MediaLibrary\Casts\AsMediaItems` | Uploaded files are owned by the model. Casts to a Collection of Files with utility methods for uploads, deletes, etc.                                                  |

Attribute classes `MediaItemAttribute` and `MediaItemsAttribute` automatically uploads files as model-owned. Further `MediaOptions` configuration options an be provided per attribute.

```php
...

use MOIREI\MediaLibrary\Casts\AsMediaItem;
use MOIREI\MediaLibrary\Casts\AsMediaItems;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'image' => AsMediaItem::class,
        'images' => AsMediaItems::class,
        'gallery' => AsMediaItems::class,
    ];

    /**
     * Optional configuration for media attribute fields
     *
     * @return array<MediaOptions>
     */
    public function mediaConfig()
    {
        return [
            'image' => MediaOptions::make()
                        ->allow('image/*'),
            'gallery' => MediaOptions::make()
                        ->allow('image/*', 'video/*'),
        ];
    }
}
```

## Accessors

You can further defined the following to help access your model's media contents.

```php

public function getImagesAttribute()
{
  return $this->media()->ofType('image')->get();
}

public function getAudiosAttribute()
{
  return $this->media()->ofType('audio')->get();
}

public function getVideosAttribute()
{
  return $this->media()->ofType('video')->get();
}
```

## Attribute Casters

### File caster

Casts a model attribute to `File`.

```php
...
use MOIREI\MediaLibrary\Casts\AsMediaFile;

class Product extends Model
{
    ...
    protected $casts = [
        'image' => AsMediaFile::class,
    ];
}
```

The file attribute can be set by the file instance, ID, or FQFN.

```php
$product = Product::find(1);
$file = File::find(...);
$product->file = $file;

// or
$product->file = $file->id;

$product->save();
```

Accessing the attribute will return a `File` type if valid.

```php
$product = Product::find(1);
expect($product->file)->toBeInstanceOf(File::class);
```

#### Array options

You can also use the array options to handle assigned file. This is particularly useful when exposing an API endpoint.

```php
$product->file = [
    'set' => $file->id,
];
```

Likewise an associated file can be detached

```php
$product->file = [
    'detach' => true,
];
```

An `UploadedFile` file can directly be uploaded and associated with the model.

```php
$product->file = [
    'upload' => $request->file('file'),
];

// or

$product->file = [
    'upload' => [
        'file' => $request->file('file'),
        'location' => 'product',
    ],
];
```

At this point, any provided file will be uploaded but the product model still needs to be persisted.

```php
$product->save();
```

### Files collection caster

Casts a model attribute to `FilesAttribute`.

```php
...
use MOIREI\MediaLibrary\Casts\AsMediaFiles;

class Product extends Model
{
    ...
    protected $casts = [
        'files' => AsMediaFiles::class,
    ];
}
```

```php
$product = Product::find(1);
$file = File::find(...);

$product->files->add($file, ...);

$product->save();
```

The `FilesAttribute` attribute also allows direct uploads

```php
$product->files->upload(
    $request->file('file')
);
```

You can also upload and set new files

```php
$product->files->uploadAndSet([
    $request->file('file1'),
    $request->file('file2'),
]);
```

#### Array options

Assigning array options can once again simplify uploads, attaching and detaching files.

```php
$model->files = [
    'set' => [$file1, $file2->id]
];
```

Attach files to existings files.

```php
$model->files = [
    'attach' => [$file1, $file2->id]
];
```

Dettach files from the existing files.

```php
$model->files = [
    'detach' => [$file1, $file2->id]
];
```

Upload files and attach them to the model.

```php
$model->files = [
    'uplpoad' => $request->file('file')
];

// or

$product->file = [
    'upload' => [
        $request->file('file'),
    ]
];

// or

$product->file = [
    'upload' => [
        'files' => [
            $request->file('file'),
        ],
        'location' => 'product',
    ],
];
```

### Media item caster

Casts a model attribute to `MediaItemAttribute`. Uploaded files are model-owned.

```php
...
use MOIREI\MediaLibrary\Casts\AsMediaItem;

class Product extends Model
{
    ...
    protected $casts = [
        'image' => AsMediaItem::class,
    ];

    /**
     * Optional configuration for media attribute fields
     *
     * @return array<MediaOptions>
     */
    public function mediaConfig()
    {
        return [
            'image' => MediaOptions::make()
                        ->allow('image/*')
                        ->convertTo('webp')
                        ->maxSize(...)
                        ->storage('app'),
        ];
    }
}
```

Uploads can further be configured per model. The above example only allows uploading images with max size. All images are also automatically converted to `webp`.

```php
$product->image->upload(
    $request->file('file')
);
```

All attributes of the underlying file can be accessed and mutated directly.

```php
$name = $product->image->name;
$name = $product->image['name'];

$product->image->name = "...";
$product->image['name'] = "...";

$product->image->save();

// or
$product->image->update([
    'name' => "...",
]);
```

#### Array options

Assigning array options can again be used to simplify uploads and deleting files.

```php
$model->image = [
    'upload' => $request->file('file'),
];

// or

$product->file = [
    'upload' => [
        'file' => [
            $request->file('file'),
        ],
        'location' => 'product',
    ],
];
```

Use the `delete` option to delete existing file.

```php
$model->files = [
    'delete' => true,
];
```

### Media items caster

Casts a model attribute to `MediaItemsAttribute`. Uploaded files are model-owned.

```php
...
use MOIREI\MediaLibrary\Casts\AsMediaItems;

class Product extends Model
{
    ...
    protected $casts = [
        'images' => AsMediaItems::class,
    ];

    /**
     * Optional configuration for media attribute fields
     *
     * @return array<MediaOptions>
     */
    public function mediaConfig()
    {
        return [
            'images' => MediaOptions::make()
                        ->allow('image/*')
                        ->convertTo('webp')
                        ->storage(function(){
                            return Storage::get('app');
                        })
        ];
    }
}
```

```php
$product->images->upload(
    $request->file('file')
);
```

You can also upload new sets of files. This will delete any existing attribute files.

```php
$product->images->uploadAndSet([
    $request->file('file1'),
    $request->file('file2'),
]);
```
