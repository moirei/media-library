<?php

namespace MOIREI\MediaLibrary;

use Closure;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\MediaStorage;

class MediaOptions
{
    public function __construct(protected array $attributes = [])
    {
        //
    }

    /**
     * Make a new instance
     * @return MediaOptions
     */
    public static function make(array $attributes = []): MediaOptions
    {
        return new MediaOptions($attributes);
    }

    /**
     * Set allowed file types.
     *
     * @param $types
     * @return MediaOptions
     */
    public function allow(...$types): MediaOptions
    {
        return $this->mime(...$types);
    }

    /**
     * Set allowed file mime types.
     *
     * @param $types
     * @return MediaOptions
     */
    public function mime(...$types): MediaOptions
    {
        $this->attributes['types'] = $types;
        return $this;
    }

    /**
     * Set max. file size.
     *
     * @param int $size
     * @return MediaOptions
     */
    public function maxSize(int $size): MediaOptions
    {
        $this->attributes['maxSize'] = $size;
        return $this;
    }

    /**
     * Crop image
     *
     * @param int $width
     * @param int $height
     * @param int[] $coordinates [x, y] displacement coordinates
     * @return MediaOptions
     */
    public function crop(int $width, int $height, array $coordinates = null): MediaOptions
    {
        $this->attributes['crop'] = [$width, $height, $coordinates];
        return $this;
    }

    /**
     * Resize image
     *
     * @param int $width
     * @param int $height
     * @param bool $upSize
     * @param bool $aspectRatio
     * @return MediaOptions
     */
    public function resize(
        int $width,
        int $height,
        bool $upSize = false,
        bool $aspectRatio = false
    ): MediaOptions {
        $this->attributes['resize'] = [$width, $height, $upSize, $aspectRatio];
        return $this;
    }

    /**
     * Convert upload image to an alternate type.
     *
     * @param string $type
     * @return MediaOptions
     */
    public function convertTo(string $type): MediaOptions
    {
        $this->attributes['imageTo'] = $type;
        return $this;
    }

    /**
     * Apply filters
     *
     * @param string[] $filters
     * @return MediaOptions
     */
    public function apply(array $filters): MediaOptions
    {
        $this->attributes['filters'] = $filters;
        return $this;
    }

    /**
     * Set privacy.
     *
     * @param string $location
     * @return MediaOptions
     */
    public function location(string $location): MediaOptions
    {
        $this->attributes['location'] = $location;
        unset($this->attributes['folder']);
        return $this;
    }

    /**
     * Set privacy.
     *
     * @param bool $private
     * @return MediaOptions
     */
    public function private(bool $private = true): MediaOptions
    {
        $this->attributes['private'] = $private;
        return $this;
    }

    /**
     * Set the storage.
     *
     * @param MediaStorage|Closure|string $storage
     * @return MediaOptions
     */
    public function storage($storage): MediaOptions
    {
        $this->attributes['storage'] = $storage;
        return $this;
    }

    /**
     * Set the folder.
     *
     * @param Folder|Closure|string $storage
     * @return MediaOptions
     */
    public function folder($folder): MediaOptions
    {
        $this->attributes['folder'] = $folder;
        unset($this->attributes['location']);
        return $this;
    }

    /**
     * Check if an attribute is set
     *
     * @param string $name
     * @return mixed
     */
    public function isset(string $name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Get an attribute
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        switch ($name) {
            case 'storage':
                return $this->getStorage();
            case 'folder':
                return $this->getFolder();
            case 'location':
                return $this->getLocation();
            case 'types':
                return Arr::get($this->attributes, 'types', config('media-library.uploads.types', $default));;
            case 'maxSize':
                return Arr::get($this->attributes, 'maxSize', config('media-library.uploads.max_size', $default));
            case 'private':
                return $this->getPrivate();
        }

        return Arr::get($this->attributes, $name, $default);
    }

    /**
     * Get the options attributes
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the storage attribute
     *
     * @return MediaStorage
     */
    protected function getStorage(): MediaStorage
    {
        $storage = Arr::get($this->attributes, 'storage');

        if (is_string($storage)) {
            return $this->attributes['storage'] = MediaStorage::get($storage);
        } else if (is_callable($storage)) {
            return $this->attributes['storage'] = $storage();
        } else {
            return MediaStorage::active();
        }

        return $storage;
    }

    /**
     * Get the folder attribute
     *
     * @return Folder|null
     */
    protected function getFolder(): Folder|null
    {
        $folder = Arr::get($this->attributes, 'folder');

        if (is_string($folder)) {
            if (Api::isUuid($folder)) {
                $folderClass = config('media-library.models.folder');
                return $this->attributes['folder'] = $folderClass::find($folder);
            }
            return $this->attributes['folder'] = $this->getStorage()->assertFolder($folder);
        } else if (is_callable($folder)) {
            return $this->attributes['folder'] = $folder();
        } else if ($location = Arr::get($this->attributes, 'location')) {
            return $this->attributes['folder'] = $this->getStorage()->assertFolder($location);
        }

        return $folder;
    }

    /**
     * Get the location option
     *
     * @return string
     */
    protected function getLocation()
    {
        $location = Arr::get($this->attributes, 'location');
        if (!$location && Arr::has($this->attributes, 'folder')) {
            $folder = $this->getFolder();
            return Api::joinPaths($folder->location, $folder->name);
        }

        return Api::formatLocation($location);
    }

    /**
     * Get the privacy option
     *
     * @return string
     */
    protected function getPrivate()
    {
        $private = Arr::get($this->attributes, 'private');
        if (is_null($private)) {
            if ($folder = $this->getFolder()) {
                return $folder->private;
            }
            return $this->getStorage()->private;
        }

        return $private;
    }
}
