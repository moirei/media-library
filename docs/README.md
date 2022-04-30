---
home: true
tagline: A Media Library package that makes working with files and dynamic images in Laravel apps a little more enjoyable.
actionText: Get Started
actionLink: /installation/prepare-models
subActionText: Install
subActionLink: /installation/
features:
  - title: ❤ Image URL manipulation
    details: Manipulate uploaded images with Cloud Image compatible API for your responsive frontend
  - title: 📜 Richtext attachments
    details: Seemlessly integrate rich text model fields with Attachments.
  - title: 💪 Simple APIs
    details: Public, signed, and protected API endpoints and classes to easily integrate with any application.
  - title: 📟 File & content sharing
    details: Share limited or extended access to uploaded files with anyone. Comes with a basic and elegant UI.
  - title: 📁 Storage system
    details: Provides media storage namespacing for multi-tenant and multi-domain uploads with varying configurations.
  - title: 🔒 Secure
    details: Configure middleware for the whole package routes or per API endpoints for fine-grained permissions and authorization.
footer: MIT Licensed | Copyright © 2021 MOIREI
---

::: slot heroText
Laravel <b class="gradient">Media</b> Library
:::

This package is intended to provide most media related needs for any Laravel application. File uploads or attachments may be associated with any Eloquent model.

## Example Usage

Assuming files content has already been uploaded and a pending attachments has been created.

### Associate files

```php
use MOIREI\MediaLibrary\Models\File;
...

[$file1, $file2] = File::take(2)->get();
$product = Product::find(1);
$product->attachFiles([$file1, $file2]);
```

### Use in attribute fields

**During create**

```php
$product = Product::create([
    'MOIREI MP202+',
    'image' => $file1,
    'gallery' => [$file1, $file2],
    'description' => $richtext, // rich text content with <img src=".." /> attachment urls
]);
```

**During update**

```php
$product = Product::find(1);
$product->image = $file;
$product->save();

...

$product->update([
    'gallery' => [$file1],
]);
```
