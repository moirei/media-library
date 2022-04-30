# Attachments

## Manual attachments

If your Eloquent models has `richTextFields` fields and the `InteractsWithMedia` trait setup, you shouldn't have to manually handle attachments.

Manually uploading attachments can be done by simply flagging the upload as at attachment.

```php
$attachment = Upload::make($uploadedFile)
        ->asAttachment()
        ->allow('jpg', 'png')
        ->save();
// or
$attachment = Upload::attachment($uploadedFile)
        ->allow('jpg', 'png')
        ->save();
// or
$attachment = Upload::uploadAttachment($uploadedFile);
```

## Associate a model

Using the `richTextFields` field

```php
$product->description = '
	...
	<img src="$attachment->url" alt="$attachment->alt" >
	...
';
$product->save();
```

Or

```php
$attachment->attach($product);
```

## Persisting attachments

Attachments are pending on creation until manually or automatically persisted by a `richTextFields` field.

You shouldn't need to do this if your models has the `richTextFields` fields and the `InteractsWithMedia` trait.

```php
use MOIREI\MediaLibrary\Models\Attachment;
...
$attachment = Attachment::where('url', $url)->first();
$attachment->persist();
```

### With the an Eloquent model

For this to work, the models must have the attachment url embedded in an `img` tag in one of its `richTextFields` fields.

This call will replace any existing model attachment with `$product`.

```php
use MOIREI\MediaLibrary\Api;

Api::persistAttachments($product);
```

## Purging attachments

```php
$attachment = Attachment::where('url', $url)->first();
$attachment->purge();
```

Purge state attachments

```php
Attachment::pruneStale();
// or specify age
Attachment::pruneStale(7); // older than 7 days
```
