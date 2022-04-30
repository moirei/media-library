<?php

namespace MOIREI\MediaLibrary\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Attributes\MediaItemsAttribute;
use MOIREI\MediaLibrary\Models\MediaStorage;

class AsMediaItems extends MediaCast
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
        return new MediaItemsAttribute(
            is_array($value) ? $fileClass::find($value) : [],
            $model,
            $key,
        );
    }

    /**
     * @param Model $model
     * @param string $key
     * @param MediaItemsAttribute $value
     * @param array $attributes
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value instanceof MediaItemsAttribute) {
            $ids = $value->map->id;
        } elseif (is_array($value) && Arr::isAssoc($value)) {
            if (!empty($attributes[$key])) {
                $ids = is_string($attributes[$key]) ? json_decode($attributes[$key], true) : [];
            } else {
                $ids = [];
            }

            if ($uploads = Arr::get($value, 'upload')) {
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
                    $upload = MediaStorage::active()->upload($upload, $options)->for($model);
                    if ($location) {
                        $upload->location($location);
                    }
                    if ($upload->validate()) {
                        $ids[] = $upload->save()->id;
                    }
                }
            } elseif ($deletes = Arr::get($value, 'delete')) {
                $deletes = Api::getFileIds($deletes);
                $fileClass = (string)config('media-library.models.file');
                foreach ($deletes as $id) {
                    optional($fileClass::get($id))->forceDelete();
                }
                $ids = array_diff($ids, $deletes);
            }
        } else {
            $ids = array_map(function ($item) {
                if (Api::isFile($item)) {
                    $item = $item->id;
                } elseif (!Api::isUuid($item)) {
                    $fileClass = (string)config('media-library.models.file');
                    $item = optional($fileClass::get($item))->id;
                }
                return $item;
            }, $value);
        }

        return [
            $key => json_encode($ids),
        ];
    }
}
