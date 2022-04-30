<?php

namespace MOIREI\MediaLibrary\Observers;

use Exception;
use MOIREI\MediaLibrary\Models\SharedContent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Exceptions\SharedContentException;

class SharedContentObserver
{
    public function creating(SharedContent $shareable)
    {
        if (!$shareable->public && count($shareable->access_keys) <= 0) {
            throw SharedContentException::requiresKeys();
        }

        if (!$shareable->access_type) {
            $shareable->access_type = SharedContent::ACCESS_TYPE_TOKEN;
        } elseif (
            ($shareable->access_type !== SharedContent::ACCESS_TYPE_SECRET) and
            ($shareable->access_type !== SharedContent::ACCESS_TYPE_TOKEN)
        ) {
            throw SharedContentException::unknownAccessType($shareable);
        }

        if ($expire_after = config('media-library.shared_content.defaults.expire_after')) {
            $shareable->expires_at = now()->addDays(intval($expire_after));
        }

        // Set defaults
        $attributes = array_keys($shareable->getAttributes());
        $table = $shareable->getTable();
        foreach (config('media-library.shared_content.defaults', []) as $key => $value) {
            if (Schema::hasColumn($table, $key) and $shareable->isFillable($key) and !in_array($key, $attributes)) {
                $shareable->setAttribute($key, $value);
            }
        }

        if ($shareable->access_type === SharedContent::ACCESS_TYPE_SECRET and !$shareable->public) {
            $shareable->access_keys = array_map(fn ($key) => SharedContent::hashKey($key), $shareable->access_keys ?? []);
        }
    }
}
