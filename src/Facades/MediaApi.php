<?php

namespace MOIREI\MediaLibrary\Facades;

use Illuminate\Support\Facades\Facade;

class MediaApi extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mediaApi';
    }
}
