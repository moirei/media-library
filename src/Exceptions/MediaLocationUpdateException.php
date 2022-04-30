<?php

namespace MOIREI\MediaLibrary\Exceptions;

class MediaLocationUpdateException extends \Exception
{
    public function __construct(string $model)
    {
        parent::__construct("$model location should not be updated");
    }
}
