<?php

namespace MOIREI\MediaLibrary\Exceptions;

class StorageDiskUpdateException extends \Exception
{
    public function __construct()
    {
        parent::__construct("Storage disk cannot be updated once in use");
    }
}
