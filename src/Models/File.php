<?php

namespace MOIREI\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use MOIREI\MediaLibrary\Api;
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
 * @property Object $responsive
 * @property Object $image
 * @property Folder $folder
 * @property Model|null $model
 * @property MediaStorage $storage
 * @property \Illuminate\Database\Eloquent\Collection $meta
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
        // 'image' => ResponsiveImage::class,
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
    public function scopeOfType($query, $type)
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
    public function scopeOfExtension($query, $extension)
    {
        return $query->where('extension', $extension);
    }

    /**
     * Prune the stale (lonely) files from the system.
     *
     * @param Carbon|int|null $age
     * @return void
     */
    public function pruneStale(Carbon | int | null $age = null)
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
     * @return Folder
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
     * Get file's public url
     *
     * @param string|File $file
     * @param Carbon|int|null $ttl
     * @return string|null
     */
    public function publicUrl(Carbon | int | null $ttl = null): string|null
    {
        if ($this->private) {
            if (is_int($ttl)) $ttl = now()->addMinutes($ttl);
            elseif (is_null($ttl)) $ttl = now()->addMinutes(30);

            if (self::isSignableDisk($this->disk())) {
                $route_name = config('media-library.route.name', '');
                return URL::temporarySignedRoute(
                    $route_name . 'file.signed',
                    $ttl,
                    ['file' => $this->id]
                );
            }
            return Storage::disk($this->disk())->temporaryUrl(
                $this->uri(),
                $ttl
            );
        }

        return Storage::disk($this->disk())->url($this->uri());
    }

    /**
     * Get file's internal url
     *
     * @param strine|File $file
     * @param Carbon|int|null $ttl
     * @return string|null
     */
    public function url(Carbon | int | null $ttl = null): string|null
    {
        $route_name = config('media-library.route.name', '');
        if ($this->private) {
            if (is_int($ttl)) $ttl = now()->addMinutes($ttl);
            elseif (is_null($ttl)) $ttl = now()->addMinutes(30);

            return URL::temporarySignedRoute(
                $route_name . 'file.signed',
                $ttl,
                ['file' => $this->id]
            );
        }

        return route($route_name . 'file', ['file' => $this->id]);
    }

    /**
     * Get file's url
     * @return string|null
     */
    public function protectedUrl(): string|null
    {
        $route_name = config('media-library.route.name', '');
        return route($route_name . 'file.protected', ['file' => $this->id]);
    }

    /**
     * Get file's download url
     *
     * @param strine|File $file
     * @param Carbon|int|null $ttl
     * @return string
     */
    public function dowloadUrl(Carbon | int | null $ttl = null): string
    {
        $route_name = config('media-library.route.name', '');
        if ($this->private) {
            if (is_int($ttl)) {
                $ttl = now()->addMinutes($ttl);
            } elseif (is_null($ttl)) {
                $ttl = now()->addMinutes(30);
            }

            return URL::temporarySignedRoute(
                $route_name . 'download.signed',
                $ttl,
                ['file' => $this->id]
            );
        }

        return route($route_name . 'download', ['file' => $this->id]);
    }
}
