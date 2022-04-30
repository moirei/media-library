<?php

namespace MOIREI\MediaLibrary\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Attributes\FilesAttribute;
use MOIREI\MediaLibrary\Models\MediaStorage;

class AsMediaFiles extends MediaCast
{
    /**
     * @param Model $model
     * @param string $key
     * @param string $value
     * @param array $attributes
     */
    public function get($model, $key, $value, $attributes)
    {
        $value = json_decode($value, true);
        $fileClass = config('media-library.models.file');
        return new FilesAttribute(
            is_array($value) ? $fileClass::find($value) : [],
            $model,
            $key,
        );
    }

    /**
     * @param Model $model
     * @param string $key
     * @param FilesAttribute|array $value
     * @param array $attributes
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value instanceof FilesAttribute) {
            $ids = $value->map->id;
        } elseif (Arr::isAssoc($value)) {
            if ($set = Arr::get($value, 'set')) {
                $ids = Api::getFileIds($set);
            } else {
                if (!empty($attributes[$key])) {
                    $ids = is_string($attributes[$key]) ? json_decode($attributes[$key], true) : [];
                } else {
                    $ids = [];
                }
                if ($attach  = Arr::get($value, 'attach')) {
                    $ids = array_unique(array_merge($ids, Api::getFileIds($attach)), SORT_REGULAR);
                }
                if ($detach  = Arr::get($value, 'detach')) {
                    $ids = array_diff($ids, Api::getFileIds($detach));
                }
                if ($uploads  = Arr::get($value, 'upload')) {
                    $options = null;
                    $location = null;
                    if (method_exists($model, 'mediaConfig')) {
                        $options = Arr::get($this->model->mediaConfig(), $key);
                    }

                    if (($is_array = is_array($uploads)) && Arr::isAssoc($uploads)) {
                        $location  = Arr::get($uploads, 'location');
                        $uploads  = Arr::get($uploads, 'files');
                    } elseif (!$is_array) {
                        $uploads = [$uploads];
                    }

                    foreach ($uploads as $upload) {
                        $upload = MediaStorage::active()->upload($upload, $options);
                        if ($location) {
                            $upload->location($location);
                        }
                        if ($upload->validate()) {
                            $ids[] = $upload->save()->id;
                        }
                    }
                }
            }
        } else {
            $ids = Api::getFileIds($value);
        }

        return [
            $key => json_encode($ids),
        ];
    }
}
