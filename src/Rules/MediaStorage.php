<?php

namespace MOIREI\MediaLibrary\Rules;

use Illuminate\Contracts\Validation\Rule;
use MOIREI\MediaLibrary\Models\MediaStorage as ModelsMediaStorage;

class MediaStorage implements Rule
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
        if (in_array($value, array_keys(config('media-library.storage.presets', [])))) {
            return true;
        }
        return !!ModelsMediaStorage::where('id', $value)->count();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be one of available media storages.';
    }
}
