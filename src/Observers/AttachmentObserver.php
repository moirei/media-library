<?php

namespace MOIREI\MediaLibrary\Observers;

use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Exceptions\MediaLocationUpdateException;
use MOIREI\MediaLibrary\Models\Attachment;

class AttachmentObserver
{
    public function deleted(Attachment $attachment)
    {
        $path = $attachment->uri();
        if (Storage::disk($attachment->disk)->exists($path)) {
            Storage::disk($attachment->disk)->delete($path);
        }
    }
}
