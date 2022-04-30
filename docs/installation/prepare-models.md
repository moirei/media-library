# Prepare Models

Ascribe the `MOIREI\MediaLibrary\Traits\InteractsWithMedia` trait to your model.

## Your model

To directly use associated media in attributes, use the provided traits.

```php
use Illuminate\Database\Eloquent\Model;
use MOIREI\MediaLibrary\Traits\InteractsWithMedia;
use MOIREI\MediaLibrary\Casts\AsMediaFile;
use MOIREI\MediaLibrary\Casts\AsMediaItems;

class Product extends Model
{
	use InteractsWithMedia;

	...

	/**
	 * The attributes that should interact with media as rich text fields.
	 *
	 * @var array
	 */
	protected $richTextFields = [
		'description',
	];

	/**
	 * The attributes that should be casted to media types.
	 * When ascribed the InteractsWithMedia trait,
	 * the file(s) referenced by these attributes are
	 * auto associated (synced) with the model.
	 *
	 * @var array
	 */
	protected $casts = [
		'image' => AsMediaFile::class,
		'gallery' => AsMediaItems::class,
	];
}
```

## Your database

**Note:** you can associate files with your models without a specific columns.
Only define columns if you want to have multiple fields with different behaviour.

```php
...
public function up()
{
  Schema::create('products', function (Blueprint $table) {
    $table->id();
    ...
    $table->text('description');
    $table->string('image'); // single file
    $table->json('gallery'); // multiple files
  });
  ...
}
```
