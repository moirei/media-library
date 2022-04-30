<?php

namespace MOIREI\MediaLibrary\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Attributes\MediaItemAttribute;
use MOIREI\MediaLibrary\Models\MediaStorage;

class AsMediaItem extends MediaCast
{
    /**
     * @param Model $model
     * @param string $key
     * @param string $value
     * @param array $attributes
     */
    public function get($model, $key, $value, $attributes)
    {
        $fileClass = config('media-library.models.file');
        return new MediaItemAttribute(
            $model,
            $key,
            $fileClass::find($value)
        );
    }

    /**
     * @param Model $model
     * @param string $key
     * @param MediaItemAttribute $value
     * @param array $attributes
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value instanceof MediaItemAttribute) {
            $value = optional($value->file())->id;
        } elseif (is_array($value)) {
            if ($upload = Arr::get($value, 'upload')) {
                $options = null;
                $location = null;
                if (method_exists($model, 'mediaConfig')) {
                    $options = Arr::get($this->model->mediaConfig(), $key);
                }
                if (is_array($upload)) {
                    $location  = Arr::get($upload, 'location');
                    $upload  = Arr::get($upload, 'file');
                }
                $upload = MediaStorage::active()->upload($upload, $options)->for($model);
                if ($location) {
                    $upload->location($location);
                }
                $upload->validate(true);
                if ($id = Arr::get($attributes, $key)) {
                    // delete any existing file
                    $fileClass = (string)config('media-library.models.file');
                    optional($fileClass::get($id))->forceDelete();
                }
                $file = $upload->save();
                $value = $file->id;
            } elseif (Arr::get($value, 'delete')) {
                if ($id = Arr::get($attributes, $key)) {
                    $fileClass = (string)config('media-library.models.file');
                    optional($fileClass::get($id))->forceDelete();
                }
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
