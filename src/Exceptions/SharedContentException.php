<?php

namespace MOIREI\MediaLibrary\Exceptions;

use MOIREI\MediaLibrary\Models\SharedContent;

class SharedContentException extends \Exception
{
    public static function requiresKeys()
    {
        return new self('Must provide access key(s) for non-public shared content');
    }
    public static function unknownAccessType(SharedContent $shareable)
    {
        return new self("Unknown access type \"$shareable->access_type\" for SharedContent");
    }
}
