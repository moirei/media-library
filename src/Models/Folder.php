<?php

namespace MOIREI\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Traits\MediaItem;
use MOIREI\MediaLibrary\Traits\UsesUuid;

/**
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $id
 * @property string $name
 * @property string $location
 * @property string $description
 * @property bool $private
 * @property object $responsive
 * @property MediaStorage $storage
 * @property string $type
 * @property Folder|null $parent
 * @property Folder|null $folder
 * @property \Illuminate\Database\Eloquent\Collection $folders
 * @property \Illuminate\Database\Eloquent\Collection $files
 * @property \Illuminate\Database\Eloquent\Collection $childFiles
 * @property \Illuminate\Database\Eloquent\Collection $meta
 *
 * @method static \Illuminate\Database\Eloquent\Builder disk(string $disk)
 * @method static \Illuminate\Database\Eloquent\Builder storage(MediaStorage|string $storage)
 */
class Folder extends Model
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
          'responsive' => 'json',
     ];

     /**
      * All of the relationships to be touched.
      *
      * @var array
      */
     protected $touches = ['parent'];

     /**
      * The relationships that should always be loaded.
      *
      * @var array
      */
     protected $with = [
          'folders',
          'files',
     ];

     /**
      * Get the folder storage.
      */
     public function storage(): BelongsTo
     {
          return $this->belongsTo(MediaStorage::class);
     }

     /**
      * Get the parent folder that the folder belongs to.
      */
     public function parent(): BelongsTo
     {
          return $this->belongsTo(config('media-library.models.folder'), 'parent_id');
     }

     /**
      * Get the parent folder that the folder belongs to.
      */
     public function folder(): BelongsTo
     {
          return $this->belongsTo(config('media-library.models.folder'), 'parent_id');
     }

     /**
      * The child folders of the folder.
      */
     public function folders(): HasMany
     {
          return $this->hasMany(config('media-library.models.folder'), 'parent_id');
     }

     /**
      * The files of the folder.
      */
     public function files(): HasMany
     {
          return $this->hasMany(config('media-library.models.file'));
     }

     /**
      * The files of all subfolders.
      */
     public function childFiles()
     {
          return $this->hasManyThrough(
               config('media-library.models.file'),
               config('media-library.models.folder'),
               'parent_id', // Foreign key on the folders table...
               'folder_id',  // Foreign key on the files table...
               'id',         // Local key on the shared_contents table...
               'id'          // Local key on the folders table...
          );
     }

     /**
      * Create a new file instance for this folder.
      * @param array $attributes
      * @return File
      */
     public function newFile(array $attributes = []): File
     {
          if (!Arr::has($attributes, 'location')) $attributes['location'] = Api::joinPaths($this->location, $this->name);
          if (!Arr::has($attributes, 'private')) $attributes['private'] = $this->private;
          /** @var File */
          $file = $this->files()->make($attributes);
          $file->storage()->associate($this->storage);

          return $file;
     }

     public function getTypeAttribute()
     {
          return 'folder';
     }

     /**
      * Get all files associated to the folder
      */
     public function getAllFilesAttribute()
     {
          return collect($this->files)->merge($this->childFiles());
     }

     /**
      * Scope query to only include folders of a given disk.
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
      * Scope query to only include folders from a given storage.
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
      * Prune the stale (empty) folders from the system.
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
               ->whereDoesntHave('folders')
               ->whereDoesntHave('files');

          if (!is_null($age)) {
               $query = $query->where('created_at', '<=', $age);
          }

          $query->chunk(100, function ($folders) {
               $folders->each->forceDelete();
          });
     }

     /**
      * Get the folder path
      *
      * @return string
      */
     public function path(): string
     {
          return $this->storage->path(
               $this->location,
               $this->name,
          );
     }

     /**
      * Set visibility
      *
      * @param bool $private
      * @return Folder
      */
     public function setPrivate(bool $private = true): Folder
     {
          DB::transaction(function () use ($private) {
               $this->update(['private' => $private]);
               Storage::disk($this->disk())->setVisibility($this->path(), Api::visibility($private));
          });

          return $this;
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
      * Get the public url
      *
      * @param Carbon|int|null $ttl
      * @return string|null
      */
     public function publicUrl($ttl = null): string|null
     {
          return null;
     }

     /**
      * Get the url
      *
      * @param Carbon|int|null $ttl
      * @return string|null
      */
     public function url($ttl = null): string|null
     {
          return $this->path();
     }

     /**
      * Get the protected url
      *
      * @return string|null
      */
     public function protectedUrl(): string|null
     {
          return null;
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
          if ($this->private) {
               if (is_int($ttl)) {
                    $ttl = now()->addMinutes($ttl);
               } elseif (is_null($ttl)) {
                    $ttl = now()->addMinutes(30);
               }

               return Api::routeSigned(
                    'download.folder.signed',
                    $ttl,
                    ['folder' => $this->id]
               );
          }

          return Api::route('download.folder', ['folder' => $this->id]);
     }

     /**
      * Zip the folder and return url to local temp file
      * @return string
      */
     public function zip(): string
     {
          $outZipPath = Api::joinPaths(sys_get_temp_dir(), Str::snake($this->name) . '-' . time() . '.zip');
          $zip = new \ZipArchive();
          $zip->open($outZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
          Api::addFolderToZip($zip, $this, $this->name);
          $zip->close();
          return $outZipPath;
     }
}
