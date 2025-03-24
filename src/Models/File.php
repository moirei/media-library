<?php

namespace MOIREI\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Casts\ResponsiveImage;
use MOIREI\MediaLibrary\Traits\MediaItem;
use MOIREI\MediaLibrary\Traits\UsesUuid;

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
 * @property Folder $folder
 * @property Model|null $model
 * @property MediaStorage $storage
 * @property \Illuminate\Database\Eloquent\Collection $meta
 *
 * @method static \Illuminate\Database\Eloquent\Builder ofType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder ofExtension(string $extension)
 * @method static \Illuminate\Database\Eloquent\Builder disk(string $disk)
 * @method static \Illuminate\Database\Eloquent\Builder storage(MediaStorage|string $storage)
 */
class File extends Model
{
    use UsesUuid, SoftDeletes, MediaItem;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'private' => 'boolean',
        'meta' => AsCollection::class,
        'image' => ResponsiveImage::class,
        'responsive' => 'json',
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['folder'];

    /**
     * Check if the file is of type
     *
     * @param string $type
     * @return bool
     */
    public function ofType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Get a file using an ID or fqfn name.
     *
     * @param string $id
     * @return File|null
     */
    public static function get(string $id): File|null
    {
        if (Api::isUuid($id)) {
            return self::find($id);
        }

        return self::firstWhere(['fqfn' => $id]);
    }

    /**
     * Get the folder storage.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function storage()
    {
        return $this->belongsTo(MediaStorage::class);
    }

    /**
     * Get the folder that the file belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function folder()
    {
        return $this->belongsTo(config('media-library.models.folder'));
    }

    /**
     * Models that are associated with this file
     *
     * @return Collection
     */
    public function getModelsAttribute()
    {
        return $this->fileables->map(function ($fileable) {
            $class = '\\' . $fileable->fileable_type;
            return $class::find($fileable->fileable_id);
        });
    }

    /**
     * The model which owns the file
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Filables of models that are associated with this file
     *
     * @return Collection
     */
    public function fileables()
    {
        return $this->hasMany(Fileable::class);
    }

    /**
     * Scope query to only include media files of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope query to only include media files of a given extension.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $$extension
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfExtension($query, string $extension)
    {
        return $query->where('extension', $extension);
    }

    /**
     * Scope query to only include media files of a given disk.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $disk
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisk($query, string $disk)
    {
        return $query->where('disk', $disk);
    }

    /**
     * Scope query to only include media files from a given storage.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  MediaStorage|string $storage
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStorage($query, $storage)
    {
        return $query->where('storage_id', is_string($storage) ? $storage : $storage->id);
    }

    /**
     * Prune the stale (lonely) files from the system.
     *
     * @param Carbon|int|null $age
     * @return void
     */
    public function pruneStale($age = null)
    {
        if (is_int($age)) {
            $age = now()->subDays($age);
        }

        $query = self::withTrashed()
            ->whereDoesntHave('fileables');

        if (!is_null($age)) {
            $query = $query->where('created_at', '<=', $age);
        }

        $query->chunk(100, function ($files) {
            $files->each->forceDelete();
        });
    }

    /**
     * Get the file location path
     *
     * @return string
     */
    public function path(): string
    {
        return $this->storage->path(
            $this->location,
            $this->id,
        );
    }

    /**
     * Get the file storage uri.
     *
     * @return string
     */
    public function uri(): string
    {
        return $this->storage->path(
            $this->location,
            $this->id,
            $this->filename
        );
    }

    /**
     * Get the file storage disk
     *
     * @return string
     */
    public function disk(): string
    {
        return $this->storage->disk;
    }

    /**
     * Check if file is an image
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Get the file content
     *
     * @return string
     */
    public function getContent()
    {
        if (!$this->exists) {
            return null;
        }

        return Storage::disk($this->disk())->get($this->uri());
    }

    /**
     * Set visibility
     *
     * @param bool $private
     * @return File
     */
    public function setPrivate(bool $private = true): File
    {
        DB::transaction(function () use ($private) {
            $this->update(['private' => $private]);
            Storage::disk($this->disk())->setVisibility($this->path(), Api::visibility($private));
        });

        return $this;
    }

    /**
     * Get file's public url. Returns dynamic route URL
     * if routeOptions is provided
     *
     * @param Carbon|int|null $ttl
     * @param array|null $routeOptions
     * @return string|null
     */
    public function publicUrl($ttl = null, ?array $routeOptions = null): string|null
    {
        $disk = $this->disk();
        $params = array_merge($routeOptions?: [], ['file' => $this->id]);

        if ($this->private) {
            if (is_int($ttl)) $ttl = now()->addMinutes($ttl);
            elseif (is_null($ttl)) $ttl = now()->addMinutes(30);

            if (Api::isSignableDisk($disk)) {
                return Storage::disk($disk)->temporaryUrl(
                    $this->uri(),
                    $ttl
                );
            }

            return Api::routeSigned('file.signed', $ttl, $params);
        }

        if(is_null($routeOptions)){
            return Storage::disk($disk)->url($this->uri());
        }

        return Api::route('file', $params);
    }

    /**
     * Get file's internal url
     *
     * @param string|File $file
     * @param Carbon|int|null $ttl
     * @return string|null
     */
    public function url($ttl = null): string|null
    {
        $params = ['file' => $this->id];

        if ($this->private) {
            if (is_int($ttl)) $ttl = now()->addMinutes($ttl);
            elseif (is_null($ttl)) $ttl = now()->addMinutes(30);

            return Api::routeSigned('file.signed', $ttl, $params);
        }

        return Api::route('file', $params);
    }

    /**
     * Get file's url
     * @return string|null
     */
    public function protectedUrl(): string|null
    {
        return Api::route('file.protected', ['file' => $this->id]);
    }

    /**
     * Get file's download url
     *
     * @param string|File $file
     * @param Carbon|int|null $ttl
     * @return string
     */
    public function downloadUrl($ttl = null): string
    {
        $params = ['file' => $this->id];

        if ($this->private) {
            if (is_int($ttl)) $ttl = now()->addMinutes($ttl);
            elseif (is_null($ttl)) $ttl = now()->addMinutes(30);

            return Api::routeSigned('download.signed', $ttl, $params);
        }

        return Api::route('download', $params);
    }
}
