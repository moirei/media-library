<?php

namespace MOIREI\MediaLibrary\Attributes;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use MOIREI\MediaLibrary\Casts\AsMediaItem;
use MOIREI\MediaLibrary\Contracts\AttributeMediaUpload;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Traits\UploadsAttributeMedia;
use Symfony\Component\HttpFoundation\File\File as SymfonyUploadedFile;

/**
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $id
 * @property string $name
 * @property string $fqfn fully qualified file name
 * @property string $location
 * @property string $description
 * @property bool $private
 * @property string $filename
 * @property string $mimetype
 * @property string $mime
 * @property string $type
 * @property string $extension
 * @property int $size
 * @property int $original_size
 * @property int $total_size
 * @property object $responsive
 * @property object $image
 * @property \MOIREI\MediaLibrary\Models\Folder $folder
 * @property Model|null $model
 * @property MediaStorage $storage
 * @property \Illuminate\Database\Eloquent\Collection $meta
 *
 * @method string path()
 * @method string uri()
 * @method string|null url()
 * @method string|null protectedUrl()
 * @method string|null downloadUrl()
 * @method string disk()
 * @method bool isImage()
 * @method string getContent()
 * @method File setPrivate(bool $private = true)
 * @method string|null publicUrl(\Carbon\Carbon | int | null $ttl = null, $routeOptions = [])
 */
class MediaItemAttribute implements Castable, AttributeMediaUpload, Arrayable, ArrayAccess
{
    use UploadsAttributeMedia;

    /**
     * @param  Model  $model
     * @param  string  $key
     * @param  File  $file
     * @return void
     */
    public function __construct(
        protected Model $model,
        protected string $key,
        protected ?File $file
    ) {
        //
    }

    /**
     * Upload a file. Deletes existing associated file
     *
     * @property UploadedFile|SymfonyUploadedFile $uploadedFile
     * @property Closure $callback
     * @return File
     */
    public function upload($uploadedFile, ?Closure $callback = null): File
    {
        return DB::transaction(function () use ($uploadedFile, $callback) {
            $this->delete();
            $options = $this->getMediaOptions();
            $upload = MediaStorage::active()->upload($uploadedFile, $options);
            if ($callback) {
                $callback($upload);
            }
            $this->file = $upload->for($this->model)->save();
            $this->model->save();

            return $this->file;
        });
    }

    /**
     * Delete the underlying file
     */
    public function delete()
    {
        if ($this->exists()) {
            $this->file->forceDelete();
        }
    }

    /**
     * Get the underlying file
     *
     * @return File
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Save changes to the underlying file
     *
     * @param  array  $options
     * @return mixed
     */
    public function save(array $options = [])
    {
        if (!$this->exists()) {
            return false;
        }
        return $this->file->save($options);
    }

    /**
     * Update the underlying file
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (!$this->exists()) {
            return false;
        }
        return $this->file->update($attributes, $options);
    }

    /**
     * CHeck if theattribute field exists with a file
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->file && $this->file->exists;
    }

    /**
     * Get the caster class to use when casting from / to this cast target.
     *
     * @param  array  $arguments
     * @return object|string
     */
    public static function castUsing(array $arguments)
    {
        return AsMediaItem::class;
    }

    /**
     * Forward all other method calls.
     */
    public function __call($name, $arguments)
    {
        return optional($this->file)->$name(...$arguments);
    }

    /**
     * Dynamically retrieve attributes on the file.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get(string $name)
    {
        return optional($this->file)->getAttribute($name);
    }

    /**
     * Dynamically set attributes on the file.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        return optional($this->file)->setAttribute($name, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return !is_null($this->__get($offset));
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        optional($this->file)->offsetUnset($offset);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return optional($this->file)->toArray();
    }
}
