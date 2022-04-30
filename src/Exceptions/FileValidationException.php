<?php

namespace MOIREI\MediaLibrary\Exceptions;

use MOIREI\MediaLibrary\Models\SharedContent;

class FileValidationException extends \Exception
{
    public static function limitExceeded()
    {
        return new self(__('File size limit exceeded'));
    }
    public static function forbiddenFormat(string $format = null)
    {
        return new self(__('Forbidden file format') . $format ? " $format" : "");
    }
    public static function unreachableUrl(string $url = null)
    {
        return new self(__('Unreachable url') . $url ? " $url" : "");
    }
}
