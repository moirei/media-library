<?php

namespace MOIREI\MediaLibrary\Attributes;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MOIREI\MediaLibrary\Casts\AsMediaItems;
use MOIREI\MediaLibrary\Contracts\AttributeMediaUpload;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Traits\UploadsAttributeMedia;
use Symfony\Component\HttpFoundation\File\File as SymfonyUploadedFile;

class MediaItemsAttribute extends Collection implements Castable, AttributeMediaUpload
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
    public function upload(UploadedFile|array $uploadedFile, ?Closure $callback = null)
    {
        $isArray = is_array($uploadedFile);
        $uploads = [];
        foreach (($isArray ? $uploadedFile : [$uploadedFile]) as $file) {
            $uploads[]  = $this->items[] = $this->uploadOne($file, $callback);
        }

        return $isArray ? $uploads : $uploads[0];
    }

    /**
     * Upload one or more files and delete all existing.
     *
     * @property UploadedFile|SymfonyUploadedFile|UploadedFile[]|SymfonyUploadedFile[] $uploadedFile
     * @property Closure $callback
     */
    public function uploadAndSet(UploadedFile|array $uploadedFile, ?Closure $callback = null)
    {
        $currentItems = $this->items;

        $isArray = is_array($uploadedFile);
        $uploads = [];
        foreach (($isArray ? $uploadedFile : [$uploadedFile]) as $file) {
            $uploads[] = $this->uploadOne($file, $callback);
        }

        array_map(fn (File $file) => $file->forceDelete(), $currentItems);
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
            $options = $this->getMediaOptions();
            $upload = MediaStorage::active()->upload($uploadedFile, $options);
            if ($callback) {
                $callback($upload);
            }
            $this->items[] = $file = $upload->for($this->model)->save();
            $this->model->save();

            return $file;
        });
    }

    /**
     * @property array|string|File $items
     */
    public function delete(array|string|File $items = null)
    {
        if ($items && !is_array($items)) {
            $items = [$items];
        }

        $items = array_map(fn ($item) => $item instanceof File ? $item->id : $item, $items);
        $ids = $this->filter(fn (File $item) => !count($items) || in_array($item->id, $items));

        $fileClass = config('media-library.models.file');
        $fileClass::forceDelete($ids);
    }

    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return AsMediaItems::class;
    }
}
