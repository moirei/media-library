<?php

namespace MOIREI\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Model;

class Fileable extends Model
{
    public function fileables()
    {
        return $this->morphTo();
    }
}
