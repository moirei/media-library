<?php

namespace MOIREI\MediaLibrary\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;

class AsMediaFile extends MediaCast
{
    /**
     * @param Model $model
     * @param string $key
     * @param string $value
     * @param array $attributes
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($value) {
            $fileClass = (string)config('media-library.models.file');
            $value = $fileClass::get($value);
        }
        return $value;
    }

    /**
     * @param Model $model
     * @param string $key
     * @param File|string $value
     * @param array $attributes
     */
    public function set($model, $key, $value, $attributes)
    {
        if (is_array($value)) {
            if ($set = Arr::get($value, 'set')) {
                if (Api::isUuid($set)) {
                    $value = $set;
                } else {
                    $fileClass = (string)config('media-library.models.file');
                    $value = optional($fileClass::get($set))->id;
                }
            } elseif ($upload  = Arr::get($value, 'upload')) {
                $options = null;
                $location = null;
                if (method_exists($model, 'mediaConfig')) {
                    $options = Arr::get($this->model->mediaConfig(), $key);
                }
                if (is_array($upload)) {
                    $location  = Arr::get($upload, 'location');
                    $upload  = Arr::get($upload, 'file');
                }
                $upload = MediaStorage::active()->upload($upload, $options);
                if ($location) {
                    $upload->location($location);
                }
                $file = $upload->save();
                $value = $file->id;
            } elseif (Arr::get($value, 'detach')) {
                $value = null;
            }
        } elseif (Api::isFile($value)) {
            $value = $value->id;
        } elseif (!Api::isUuid($value)) {
            $fileClass = (string)config('media-library.models.file');
            $value = optional($fileClass::get($value))->id;
        }

        return [$key => $value];
    }
}
