<?php

namespace MOIREI\MediaLibrary\Casts;

use ArrayObject;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\Folder;

class ResponsiveImage extends MediaCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  File|Folder $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return object|null
     */
    public function get($model, $key, $value, $attributes)
    {
        // public files may have responsive images saved
        $value = is_string($value) ? json_decode($value) : $value;

        if (!$model->private and !empty($value)) return $value;

        $images = $value ? Api::getResponsivePublicUrl($model) : Api::placeholderImages();

        return new ArrayObject($images);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  File|Folder  $model
     * @param  string  $key
     * @param  mixed $value
     * @param  array  $attributes
     * @return string|null
     */
    public function set($model, $key, $value, $attributes)
    {
        return is_string($value) ? $value : json_encode($value);
    }
}
