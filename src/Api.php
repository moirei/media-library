<?php

namespace MOIREI\MediaLibrary;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use League\Flysystem\Util;
use MOIREI\MediaLibrary\Models\Attachment;
use MOIREI\MediaLibrary\Traits\InteractsWithMedia;

class Api
{
    /**
     *
     * @param string|File|array $files
     * @return Model|array
     */
    public static function getMedia(string | File | array $files)
    {
        $is_array = is_string($files);

        if (!$is_array) $files = [$files];

        if (!count($files)) {
            return $is_array ? [] : null;
        }

        $class = config('media-library.models.file');
        $files = collect($files)
            ->map(fn ($file) => is_string($file) ? $class::find($file) : $file)
            ->map(fn ($file) => $file->toArray());

        return $is_array ? $files : $files[0];
    }

    /**
     * Get a general placeholder images
     *
     * @return array
     */
    public static function placeholderImages(): array
    {
        $images = [
            'alt' => 'Placeholder image',
        ];

        foreach (config('media-library.uploads.images.sizes') as $size => $v) {
            $images[$size] = config('media-library.placeholder');
        }

        return $images;
    }

    /**
     * Join paths
     *
     * TODO: use `DIRECTORY_SEPARATOR` instead of `/`??
     * @example joinPaths('my/paths/', '/are/', 'a/r/g/u/m/e/n/t/s/')
     * @return string
     */
    public static function joinPaths()
    {
        $paths = [];

        foreach (func_get_args() as $path) {
            if (!!$path) {
                $paths[] = $path;
            }
        }

        return Util::normalizePath(join(DIRECTORY_SEPARATOR, $paths), DIRECTORY_SEPARATOR);
    }

    /**
     * Get visbilitye
     *
     * @param bool $private
     * @return string
     */
    public static function visibility(bool $private): string
    {
        return $private ? 'private' : 'public';
    }

    /**
     * Check if disk is route signable
     *
     * @param string $disk
     * @return bool
     */
    public static function isSignableDisk(string $disk): bool
    {
        return $disk === 'local';
    }

    /**
     * Check if item is a folder
     *
     * @param Model|string $item
     * @return bool
     */
    public static function isFolder(Model|string $item): bool
    {
        return (is_string($item) && $item === Folder::class) ||
            (!is_string($item) && $item instanceof Folder) ||
            is_subclass_of($item, Folder::class);
    }

    /**
     * Check if item is a file
     *
     * @param Model|string $item
     * @return bool
     */
    public static function isFile(Model|string $item): bool
    {
        return (is_string($item) && $item === File::class) ||
            (!is_string($item) && $item instanceof File) ||
            is_subclass_of($item, File::class);
    }

    /**
     * Get all root folders
     *
     * @return Folder
     */
    public static function rootFolders(): Collection
    {
        $base_path = rtrim(self::joinPaths('/', config('media-library.folder', '')));
        $folderClass = config('media-library.models.folder');
        return $folderClass::where('location', $base_path)->get();
    }

    /**
     * Get all root files
     *
     * @return File
     */
    public static function rootFiles(): Collection
    {
        $base_path = rtrim(self::joinPaths('/', config('media-library.folder', '')));
        $fileClass = config('media-library.models.file');
        return $fileClass::where('location', $base_path)->get();
    }

    /**
     * Set visibility
     *
     * @param Model $item
     * @return void
     */
    public static function setVisibility(Model $item): void
    {
        if (self::isFolder($item)) {
            $path = Api::joinPaths($item->location, $item->name);
        } else {
            $path = Api::joinPaths($item->location, $item->id);
        }

        Storage::disk($item->disk)->setVisibility($path, Api::visibility($item->private));
    }

    /**
     * Get file class key
     *
     * @return string
     */
    public static function fileClassKey(): string
    {
        $fileClass = config('media-library.models.file');
        return app($fileClass)->getKeyName();
    }

    /**
     * Get folder class key
     *
     * @return string
     */
    public static function folderClassKey(): string
    {
        $folderClass = config('media-library.models.folder');
        return app($folderClass)->getKeyName();
    }

    /**
     * Check if string is UUID
     *
     * @param string $str
     * @return bool
     */
    public static function isUuid(string $str)
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $str) === 1;
    }

    /**
     * Get file type
     * @param string mimetype
     * @param string extension
     * @return string
     */
    public static function getFileType(string $mimetype, string $extension = null): string
    {
        [$basetype, $subtype] = explode('/', "$mimetype/"); // predense with '/' to ensure element in array destruction
        $extension = $extension ? $extension : $subtype;
        if ($subtype === 'svg+xml') $subtype = 'svg';
        foreach (config('media-library.types') as $type => $subtypes) {
            if (in_array($subtype, $subtypes) || in_array($extension, $subtypes)) return $type;
        }

        if ($basetype === 'application') return 'other';

        return $subtype ?: $basetype;
    }

    /**
     * Get file subtype
     * @param string mimetype
     * @return string
     */
    public static function getSubType(string $mimetype): string
    {
        [$type, $subtype] = explode('/', $mimetype);
        if ($subtype === 'svg+xml') $subtype = 'svg';
        return $subtype ?: $type;
    }

    /**
     * Persist pending attachment.
     *
     * @param string $url
     * @return void
     */
    public function persistAttachment(string $url)
    {
        Attachment::where('url', $url)
            ->get()
            ->each
            ->persist();
    }

    /**
     * Persist all pending attachments in the models' text fields
     *
     * @param Model $model
     * @param bool $force force operation
     * @return void
     */
    public static function persistAttachments(Model $model, bool $force = false)
    {
        $richtext_match = config('media-library.uploads.attachments.richtext_match', []);
        if (!count($richtext_match)) {
            return;
        }

        $urls = [];
        $fields = $model->richTextFields();

        foreach ($fields as $field) {
            if ($model->isClean($field) && !$force) continue;
            $content = Arr::get($model, $field, '');
            if (!empty($content)) {
                foreach ($richtext_match as $match) {
                    $matches = [];
                    preg_match_all($match, $content, $matches);
                    if (count($matches[1]) !== 0) $urls = array_merge($urls, $matches[1]);
                }
            }
        }

        $attachments = Attachment::where('pending', true)->whereIn('url', $urls)->get();

        // perist and associate all with model
        $attachments->each(fn (Attachment $attachment) => $attachment->persist()->attach($model));
    }

    /**
     * Get provided files' IDs
     *
     * @param Array<File|string> $files
     * @return string[]
     */
    public static function getFileIds($files)
    {
        return array_map(function ($item) {
            if (Api::isFile($item)) {
                $item = $item->id;
            } elseif (!Api::isUuid($item)) {
                $fileClass = (string)config('media-library.models.file');
                $item = optional($fileClass::get($item))->id;
            }
            return $item;
        }, $files);
    }

    /**
     * Check if model interacts with media
     *
     * @return bool
     */
    public static function interactsWithMedia($model)
    {
        return in_array(InteractsWithMedia::class, class_uses_recursive($model));
    }
}
