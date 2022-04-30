<?php

namespace MOIREI\MediaLibrary\Observers;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Models\Folder;

class FolderObserver
{
    /**
     * @param Folder $folder
     */
    public function creating(Folder $folder)
    {
        /** @var Folder */
        $parent = $folder->location ?
            $folder->storage->resolveFolder($folder->location, false, true) :
            $folder->storage->resolveFolder('/');

        if ($parent) {
            $folder->location = Api::joinPaths($parent->location, $parent->name);
        }

        if (!isset($folder->private)) {
            $folder->private = $parent ? $parent->private : $folder->storage->private;
        }

        if (Schema::hasColumn($folder->getTable(), 'image')) {
            if (!$folder->private and !empty($folder->responsive)) {
                // $folder->image = $folder->storage->getResponsivePublicUrl($folder);
            }
        }

        Storage::disk($folder->disk())->makeDirectory($folder->path());
    }

    /**
     * @param Folder $folder
     */
    public function updating(Folder $folder)
    {
        if ($folder->isDirty('private')) {
            Storage::disk($folder->disk())->setVisibility($folder->path(), Api::visibility($folder->private));

            // update child folders privacy
            $folder->folders()->update([
                'private' => $folder->private,
            ]);

            // update files privacy
            $folder->files()->update([
                'private' => $folder->private,
            ]);
        }
    }

    /**
     * @param Folder $folder
     */
    public function updated(Folder $folder)
    {
        if ($folder->isDirty('location')) {
            $location = Api::joinPaths($folder->location, $folder->name);

            $folder->folders->each(function ($folder) use ($location) {
                $folder->update([
                    'location' => $location,
                ]);
            });

            // update files location
            $folder->files()->update([
                'location' => $location,
            ]);
        }
    }

    /**
     * @param Folder $folder
     */
    public function deleted(Folder $folder)
    {
        if (
            in_array(SoftDeletes::class, class_uses_recursive($folder)) &&
            !$folder->isForceDeleting()
        ) {

            // only required if self-ref relation is not at DB level
            $folder->folders()->delete();
            $folder->shares()->delete();
            return;
        }

        // only required if self-ref relation is not at DB level
        $folder->folders()->forceDelete();
        $folder->shares()->forceDelete();

        Storage::disk($folder->disk())->deleteDirectory($folder->path());
    }
}
