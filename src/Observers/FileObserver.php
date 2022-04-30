<?php

namespace MOIREI\MediaLibrary\Observers;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Models\File;

class FileObserver
{
    /**
     * @param File $file
     */
    public function updating(File $file)
    {
        if ($file->isDirty('private')) {
            Storage::disk($file->disk())->setVisibility($file->path(), Api::visibility($file->private));
        }
    }

    /**
     * @param File $file
     */
    public function deleted(File $file)
    {
        if (
            in_array(SoftDeletes::class, class_uses_recursive($file)) &&
            !$file->isForceDeleting()
        ) {
            // only required if self-ref relation is not at DB level
            $file->shares()->delete();
            return;
        }

        // only required if self-ref relation is not at DB level
        $file->shares()->forceDelete();

        Storage::disk($file->disk())->deleteDirectory($file->path());
    }
}
