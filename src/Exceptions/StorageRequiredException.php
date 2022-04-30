<?php

namespace MOIREI\MediaLibrary\Exceptions;

class StorageRequiredException extends \Exception
{
    public function __construct()
    {
        parent::__construct(__('Please provide a Storage'));
    }
}
