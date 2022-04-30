# Concepts

## Media storage

The media storage allows your application to save domain-specific files and folders in isolation. Storage disk, location, size, privacy, etc. can be managed at this level.

> Note that a model can make use of files from different storages.

A use case may be setting the active Storage in a middleware against an authenticated user.

## Imaging

This package comes packed with multiple features including the ability to dynamically resize and filter uploaded images. This concept here is called _Imaging_. Handled by the `MagicImager` class, this package essentially provides dynamic imaging via urls manipulation similar to [cloudimage.io](https://www.cloudimage.io/) and other imaging servers.

For Vue lovers, [vue-imager](https://github.com/moirei/vue-imager) is a package that makes it very easy to display rich media served by this package.

## File identification

Files are identified by their UUID or Fully Qualified File Name (FQFN). A file's UUID is the prefered identification key while its FQFN is provided for client & route friendly urls.

The `find` method retrieves files by UUID. To retrieve by either UUID or FQFN, use the `get` method.

```php
$file = File::get(...);
```

## Files types

Generally files are upload unto a storage and made available for all models. This allows your application to provide a centralised media library for any model. However, you can upload files owned by a model, disallowing use by any other models. Model-owned files are by default hidden from _browse_ results.

When using casts, `AsMediaFile` and `AsMediaFiles` casts are used to set shared files amongst models. For model-owned files use the `AsMediaItem` and `AsMediaItems` casts.

## File type and mime

Allowed file types are pre-set in the media-library config with generic types such as `image`, `audio`, `video` and `doc` to capture basic file categories. While a file's `mimetype` attribute contains the full file mimetype, its `mime` attribute is the shortened (subtype). For example, uploaded JPG image will have type `image`, mimetype `image/jpg` and saved with mime `jpg`.

## Attachments

Attachments are intended for rich-text fields and are not to be confused with Files. They exist in conjunction with uploaded files and are generally hidden from the user.
All attachments are pending until persisted. If your model is setup with `richTextFields` defined, created attachments will be automatically detected and persisted.

### Attachables

Attachments are primarily intended for embedding images in rich-text applications.

It's assumed that an attachment is an image file. Therefore automatically persisting attachments only scans for `img` tags in the rich text fields. Feel free to add more _regex_ expressions in `attachments.richtext_match` of your config.

## Directory system

While created files and folders are represented as Eloquent Models, their actual content are persistent in Storage according to the disk of their `MediaStorage`. File and folder locations are relative to their MediaStorage location and `folder` of the media-library config.

## File privacy

File and folder privacy can be used to set visibility to end users. Private files can be temporarily made public.

> Note that private files/folders stored in local public disks may still be publically accessible
