<?php

namespace MOIREI\MediaLibrary\Attributes;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MOIREI\MediaLibrary\Casts\AsMediaFiles;
use MOIREI\MediaLibrary\Contracts\AttributeMediaUpload;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Traits\UploadsAttributeMedia;
use Symfony\Component\HttpFoundation\File\File as SymfonyUploadedFile;

class FilesAttribute extends Collection implements Castable, AttributeMediaUpload
{
    use UploadsAttributeMedia;

    /**
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $items
     * @return void
     */
    public function __construct(
        $items = [],
        protected ?Model $model = null,
        protected ?string $key = null,
    ) {
        parent::__construct($items);
    }

    /**
     * Get underlying files names
     * @return string[]
     */
    public function names()
    {
        return $this->map->name;
    }

    /**
     * Upload one or more files.
     *
     * @property UploadedFile|SymfonyUploadedFile|UploadedFile[]|SymfonyUploadedFile[] $uploadedFile
     * @property Closure $callback
     */
    public function upload(UploadedFile $uploadedFile, ?Closure $callback = null)
    {
        $isArray = is_array($uploadedFile);
        $uploads = [];
        foreach (($isArray ? $uploadedFile : [$uploadedFile]) as $file) {
            $uploads[]  = $this->items[] = $this->uploadOne($file, $callback);
        }

        return $isArray ? $uploads : $uploads[0];
    }

    /**
     * Upload one or more files and remove all existing.
     *
     * @property UploadedFile|SymfonyUploadedFile|UploadedFile[]|SymfonyUploadedFile[] $uploadedFile
     * @property Closure $callback
     */
    public function uploadAndSet(UploadedFile|array $uploadedFile, ?Closure $callback = null)
    {
        $isArray = is_array($uploadedFile);
        $uploads = [];
        foreach (($isArray ? $uploadedFile : [$uploadedFile]) as $file) {
            $uploads[] = $this->uploadOne($file, $callback);
        }
        $this->items = $uploads;

        return $isArray ? $uploads : $uploads[0];
    }

    /**
     * Upload a file.
     *
     * @property UploadedFile|SymfonyUploadedFile $uploadedFile
     * @property Closure $callback
     * @return File
     */
    protected function uploadOne(UploadedFile $uploadedFile, ?Closure $callback = null): File
    {
        return DB::transaction(function () use ($uploadedFile, $callback) {
            $upload = MediaStorage::active()->upload($uploadedFile);
            if ($callback) {
                $callback($upload);
            }
            $this->items[] = $file = $upload->save();
            $this->model->save();

            return $file;
        });
    }

    /**
     * Add an item to the collection.
     *
     * @param $items
     * @return self
     */
    public function add(...$items)
    {
        foreach ($items as $item) {
            if (is_string($item)) {
                $fileClass = (string)config('media-library.models.file');
                $item = $fileClass::get($item);
            }
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * Add an item to the collection.
     *
     * @param $items
     * @return self
     */
    public function remove(...$items)
    {
        $items = array_map(fn ($item) => is_string($item) ? $item : $item->id, $items);
        $this->items = array_filter($this->items, fn ($item) => !in_array($item->id, $items));

        return $this;
    }

    /**
     * @param File[]|string[] $item
     * @return self
     */
    public function set(array $items)
    {
        $fileClass = (string)config('media-library.models.file');
        $this->items = array_map(fn ($item) => is_string($item) ? $fileClass::get($item) : $item, $items);
        return $this;
    }

    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return AsMediaFiles::class;
    }
}
