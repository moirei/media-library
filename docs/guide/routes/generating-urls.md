# Generating Urls

## Public Urls

Generate a public url for a file.

For private files, a signed or temporal url with a default 30s TTL is returned.

```php
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Models\File;
...

$file = File::find('file-id');
$url = $file->publicUrl();
// Or
$url = $file->publicUrl(now()->addMinutes(60));
// Or
$url = (string)$file; // casting files/folders to string will return a public url
```

## Protected Urls

Protected routes provide an additional layer of security. Actual protection is decided by the middleware you configure for the route group.

To fully set it up, define middles for `file.protected` in `route.middleware` of your config.

```php
$url = $file->protectedUrl();
```

## Local Urls

Get a url local to your application. The url will include `yourdomain.com` regardless of the file's storage driver.

For private files, the url is signed with a default 30s TTL.

```php
$url = $file->url();
// Or
$url = $file->url(now()->addMinutes(60));
```

## Download Urls

Generated url can be used to automatically download the file.

For private files, the url is signed with a default 30s TTL.

```php
$url = $file->downloadUrl();
// Or
$url = $file->downloadUrl(now()->addMinutes(60));
```
