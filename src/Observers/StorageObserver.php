<?php

namespace MOIREI\MediaLibrary\Observers;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Exceptions\MediaLocationUpdateException;
use MOIREI\MediaLibrary\Exceptions\StorageDiskUpdateException;
use MOIREI\MediaLibrary\Models\MediaStorage;

class StorageObserver
{
    /**
     * @param MediaStorage $storage
     */
    public function creating(MediaStorage $storage)
    {
        if (!$storage->location) {
            $storage->location = Str::slug($storage->name);
        }
        if (!$storage->disk) {
            $storage->disk = config('media-library.storage.disk', 'local');
        }
        if (is_null($storage->private)) {
            $storage->private = config('media-library.storage.private', false);
        }
    }

    /**
     * @param MediaStorage $storage
     */
    public function updating(MediaStorage $storage)
    {
        if (!$storage->isEmpty()) {
            if ($storage->isDirty('location')) {
                throw new MediaLocationUpdateException('Storage');
            }
            if ($storage->isDirty('disk')) {
                throw new StorageDiskUpdateException;
            }
        }

        if ($storage->isDirty('private')) {
            Storage::disk($storage->disk)->setVisibility($storage->path(), Api::visibility($storage->private));
        }
    }

    /**
     * @param MediaStorage $storage
     */
    public function deleted(MediaStorage $storage)
    {
        if (
            in_array(SoftDeletes::class, class_uses_recursive($storage)) &&
            !$storage->isForceDeleting()
        ) {
            // only required if self-ref relation is not at DB level
            $storage->folders()->delete();
            return;
        }

        // only required if self-ref relation is not at DB level
        $storage->folders()->forceDelete();

        Storage::disk($storage->disk)->deleteDirectory(Api::joinPaths($storage->location, $storage->id));
    }
}
