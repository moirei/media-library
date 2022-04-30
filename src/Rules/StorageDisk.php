<?php

namespace MOIREI\MediaLibrary\Rules;

use Illuminate\Contracts\Validation\Rule;

class StorageDisk implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return in_array($value, array_keys(config('filesystems.disks', [])));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be one of available filesystem disks.';
    }
}
