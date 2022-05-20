<?php

namespace MOIREI\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Traits\UsesUuid;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use MOIREI\MediaLibrary\Exceptions\StorageRequiredException;
use MOIREI\MediaLibrary\MediaOptions;
use MOIREI\MediaLibrary\Upload;
use Symfony\Component\HttpFoundation\File\File as SymfonyUploadedFile;

/**
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $id
 * @property string $name
 * @property string $location
 * @property string $description
 * @property string $disk
 * @property bool $private
 * @property int $capacity
 * @property \Illuminate\Database\Eloquent\Collection $folders
 * @property \Illuminate\Database\Eloquent\Collection $files
 * @property \Illuminate\Database\Eloquent\Collection $meta
 *
 * @method static \Illuminate\Database\Eloquent\Builder disk(string $disk)
 */
class MediaStorage extends Model
{
    use UsesUuid, SoftDeletes;

    /**
     * The storage table.
     *
     * @var string
     */
    protected $table = 'storages';

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
    ];

    /**
     * The active global storage instance
     *
     * @var MediaStorage
     */
    protected static MediaStorage $storage;

    /**
     * Scope query to only include storages of a given disk.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisk($query, $disk)
    {
        return $query->where('disk', $disk);
    }

    /**
     * Set the global Storage instance to use.
     *
     * @param MediaStorage|string $storage
     * @return MediaStorage
     */
    public static function use(MediaStorage|string $storage): MediaStorage
    {
        if (is_string($storage)) {
            $storage = self::get($storage);
            if (!$storage) {
                throw new StorageRequiredException();
            }
        }
        self::$storage = $storage;
        return $storage;
    }

    /**
     * Create and use.
     *
     * @param array $attributes
     * @return MediaStorage
     */
    public static function createAndUse(array $attributes): MediaStorage
    {
        return self::use(self::create($attributes));
    }

    /**
     * Get a storage instance via id or config name.
     *
     * @param string $name
     * @return MediaStorage|null
     */
    public static function get(string $name): MediaStorage|null
    {
        $storage = null;
        if (Api::isUuid($name)) {
            $storage = self::find($name);
        } else {
            $config = config("media-library.storage.presets.$name");
            if ($config) {
                $config = Arr::only($config, ['name', 'location', 'disk']);
                $storage = self::firstOrCreate($config);
            }
        }
        return $storage;
    }

    /**
     * Get the active global storage
     *
     * @return MediaStorage
     */
    public static function active(): MediaStorage
    {
        if (!isset(self::$storage)) {
            self::use(config("media-library.storage.default"));
        }
        return self::$storage;
    }

    /**
     * Folders in this storage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function folders()
    {
        return $this->hasMany(config('media-library.models.folder'), 'storage_id');
    }

    /**
     * Files in this storage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(config('media-library.models.file'), 'storage_id');
    }

    /**
     * Create a new file instance for this storage.
     *
     * @param array $attributes
     * @return File
     */
    public function newFile(array $attributes = []): File
    {
        if (!Arr::has($attributes, 'private')) $attributes['private'] = $this->private;
        /** @var File */
        $file = $this->files()->make($attributes);
        $file->storage()->associate($this);

        return $file;
    }

    /**
     * Create a new folder
     *
     * @param array $attributes
     */
    public function createFolder(array $attributes): Folder
    {
        $location = Arr::pull($attributes, 'location');
        if (!Arr::has($attributes, 'private')) $attributes['private'] = $this->private;

        if ($location) {
            if (Api::isFolder($location)) {
                $location = Api::joinPaths($location->location, $location->name);
            } else {
                $parent = $this->assertFolder($location);
            }
            $attributes['location'] = $location;
        }

        /** @var Folder */
        $folder = $this->folders()->make($attributes);
        $folder->storage()->associate($this);

        if (isset($parent) && Api::isFolder($parent)) {
            $folder->parent()->associate($parent);
        }

        $folder->save();

        return $folder;
    }

    /**
     * Whether or not the storage path exists
     * @return bool
     */
    public function exists()
    {
        return Storage::disk($this->disk)->exists($this->path());
    }

    /**
     * Whether or not the storage is empty
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->folders()->count() && !$this->files()->count();
    }

    /**
     * Delete all storage content
     *
     * @param bool $force
     */
    public function deleteAllContent(bool $force = false)
    {
        if ($force) {
            $this->folders()->forceDelete();
            $this->files()->forceDelete();
            Storage::disk($this->disk)->deleteDirectory($this->path());
        } else {
            $this->folders()->delete();
            $this->files()->delete();
        }
    }

    /**
     * Get the storage path or wrt the given location
     *
     * @param $locations
     * @return string
     */
    public function path(...$locations): string
    {
        return Api::joinPaths(
            config('media-library.folder', 'media'),
            config('media-library.uploads.location', 'files'),
            $this->location,
            ...$locations,
        );
    }

    /**
     * Get folder or create one if it doesnt exist
     *
     * @param string $location
     * @return Folder
     */
    public function assertFolder(string $location): Folder
    {
        $data = Api::extractPathFolder($location);

        if (!$data['name']) {
            throw new \InvalidArgumentException("Cannot assert empty folder");
        }

        $folderClass = config('media-library.models.folder');
        $folder = $folderClass::firstWhere($data);

        if ($folder) return $folder;

        $parent = $this->resolveFolder($location);

        if ($parent) {
            $data['private'] = $parent->private;
            $folder = $parent->folders()->create($data);
        } else {

            $data['private'] = $this->private;
            $segments = explode('/', trim($location, '/'));

            if (count($segments) == 0) {
                // this is a root path.
                return null;
            }
            if (count($segments) == 1) {
                return $this->folders()->create($data);
            }

            $first = array_shift($segments);
            $last = array_pop($segments);

            $parent = $this->assertFolder($first);
            foreach ($segments as $name) {
                $where = [
                    'name' => $name,
                    'location' => Api::joinPaths($parent->location, $parent->name),
                ];
                $parent = Api::getOrCreateChildFolder($parent, $where, $data);
            }
            $where = [
                'name' => $last,
                'location' => Api::joinPaths($parent->location, $parent->name),
            ];
            $folder =  Api::getOrCreateChildFolder($parent, $where, $data);
        }

        return $folder;
    }

    /**
     * Resolve folder from location string
     *
     * @param string $location
     * @param bool $create
     * @return Folder|null
     */
    public function resolveFolder(
        string $location,
        bool $create = false,
        bool $absolute = false,
    ): Folder | null {
        if ($absolute) {
            $path = Api::joinPaths(
                config('media-library.folder'),
                $location,
            );
        } else {
            $path = $location;
        }

        $folderClass = config('media-library.models.folder');
        $folder = $folderClass::firstWhere(Api::extractPathFolder($path));

        if ($create and !$folder) {
            $folder = $this->assertFolder($path, true);
        }

        return $folder;
    }

    /**
     * Browse location and return all media items
     *
     * @param string $location
     * @param array $options
     * @return Collection
     */
    public function browse(string $location = null, array $options = []): Collection
    {
        $modelFiles = Arr::get($options, 'modelFiles', false);
        $filesOnly = Arr::get($options, 'filesOnly', false);
        $type = Arr::get($options, 'type');
        $mime = Arr::get($options, 'mime');
        $private = Arr::get($options, 'private');

        $fileQuery = $this->files()
            ->when($modelFiles, function ($query) {
                $query->whereNotNull('model_id');
            })->when($type, function ($query) use ($type) {
                $query->whereIn('type', array($type));
            })->when($mime, function ($query) use ($mime) {
                $query->whereIn('mime', array($mime));
            })->when(!is_null($private), function ($query) use ($private) {
                $query->where('private', $private);
            });

        $this->whereLocation($fileQuery, $location);

        $files = $fileQuery->get();

        if (!$filesOnly && is_null($type) && is_null($mime)) {
            $folders = $this->whereLocation($this->folders(), $location)->get();
            return $folders->merge($files);
        }

        return $files;
    }

    /**
     * Create a upload
     * Profile a file request input, public url or local path
     *
     * @param UploadedFile|SymfonyUploadedFile|string $uploadable
     * @param MediaOptions $options
     * @return Upload
     */
    public function upload(UploadedFile | SymfonyUploadedFile | string $uploadable, ?MediaOptions $options = null): Upload
    {
        if ($options && !$options->isset('storage')) {
            $options->storage($this);
        } else {
            $options = new MediaOptions([
                'private' => $this->private,
                'storage' => $this,
            ]);
        }

        if (is_string($uploadable)) {
            return Upload::fromUrl($uploadable);
        } elseif (get_class($uploadable) === SymfonyUploadedFile::class) {
            $uploadable = new UploadedFile($uploadable->getRealPath(), $uploadable->getFilename(), $uploadable->getMimeType());
        }

        return Upload::make($uploadable, $options);
    }

    /**
     * Set visibility
     *
     * @param bool $private
     * @return MediaStorage
     */
    public function setPrivate(bool $private = true): MediaStorage
    {
        DB::transaction(function () use ($private) {
            $this->update(['private' => $private]);
            Storage::disk($this->disk)->setVisibility($this->path(), Api::visibility($private));
        });

        return $this;
    }

    /**
     * Move a file to a new lcation
     *
     * @param File $file
     * @param Folder|string $location
     * @return void
     */
    public function move(File $file, Folder | string $location)
    {
        if (is_string($location)) {
            if (Api::isUuid($location)) {
                $folder = $this->findFolder($location);
                if (!$folder) return false;
            } else {
                $location = Api::formatLocation($location);
                if (!$location) {
                    // move to root
                    $file->folder()->dissociate();
                    $file->update(['location' => null]);
                    return;
                }
                $folder = $this->assertFolder($location);
            }
        } else {
            $folder = $location;
        }
        $location = Api::joinPaths(
            $folder->location,
            $folder->name,
        );

        $path = $this->path($location, $file->id);
        if (Storage::disk($this->disk)->exists($path)) {
            return;
        }

        Storage::disk($this->disk)->move($file->path(), $path);

        $file->location = $location;
        $file->folder()->dissociate();

        if (isset($folder)) {
            $file->folder()->associate($folder);
        }

        $file->save();
    }

    /**
     * Move a folder to a new lcation
     *
     * @param Folder $folder
     * @param Folder|string $location
     * @return void
     */
    public function moveFolder(Folder $folder, Folder | string $location)
    {
        if (is_string($location)) {
            if (Api::isUuid($location)) {
                $parent = $this->findFolder($location);
                if (!$parent) return false;
            } else {
                $location = Api::formatLocation($location);
                if (!$location) {
                    // move to root
                    $folder->folder()->dissociate();
                    $folder->update(['location' => null]);
                    return;
                }
                $parent = $this->assertFolder($location);
            }
        } else {
            $parent = $location;
        }
        $location = Api::joinPaths(
            $parent->location,
            $parent->name,
        );

        $newLocation = Api::joinPaths($location, $folder->name);
        $path = $this->path($newLocation);

        Storage::disk($this->disk)->move($folder->path(), $path);

        $folder->location = $location;
        $folder->folder()->dissociate();

        if (isset($parent)) {
            $folder->folder()->associate($parent);
        }

        $folder->save();

        $folder->files()->update(['location' => $newLocation]);

        // fetch and update recursively (in folder observer)
        $folder->folders->each(function ($folder) use ($newLocation) {
            $folder->update([
                'location' => $newLocation,
            ]);
        });
    }

    /**
     * Find a file in this storage
     *
     * @param array|string $where
     * @return File
     */
    public function findFile(array|string $where)
    {
        if (is_string($where)) {
            $where = [Api::isUuid($where) ? 'id' : 'fqfn' => $where];
        }

        return  $this->files()->firstWhere($where);
    }

    /**
     * Find a file in this storage
     *
     * @param array|string $where
     * @return Folder
     */
    public function findFolder(array|string $where)
    {
        if (is_string($where)) {
            if (Api::isUuid($where)) {
                $where = ['id' => $where];
            } else {
                $where = Api::extractPathFolder($where);
            }
        }

        return  $this->folders()->firstWhere($where);
    }

    protected function whereLocation($query, $location)
    {
        $query->when($location, function ($query) use ($location) {
            $query->where('location', $location);
        })->when(!$location, function ($query) {
            $query->where(function ($query) {
                $query->where('location', '')
                    ->orWhereNull('location');
            });
        });
        return $query;
    }
}
