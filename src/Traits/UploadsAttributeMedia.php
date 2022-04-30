<?php

namespace MOIREI\MediaLibrary\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MOIREI\MediaLibrary\MediaOptions;

trait UploadsAttributeMedia
{
    /**
     * Get the media options
     *
     * @return MediaOptions|null
     */
    public function getMediaOptions(): MediaOptions|null
    {
        if (isset($this->model) && method_exists($this->model, 'mediaConfig')) {
            return Arr::get($this->model->mediaConfig(), $this->getAttributeName());
        }

        return null;
    }

    /**
     * Get the attribute name
     *
     * @return string
     */
    public function getAttributeName(): string
    {
        return $this->key;
    }
}
