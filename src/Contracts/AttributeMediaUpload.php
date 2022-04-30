<?php

namespace MOIREI\MediaLibrary\Contracts;

use MOIREI\MediaLibrary\MediaOptions;

interface AttributeMediaUpload
{
    /**
     * Get the media options
     *
     * @return MediaOptions|null
     */
    public function getMediaOptions(): MediaOptions|null;

    /**
     * Get the attribute name
     *
     * @return string
     */
    public function getAttributeName(): string;
}
