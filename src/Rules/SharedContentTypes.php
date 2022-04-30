<?php

namespace MOIREI\MediaLibrary\Rules;

use Illuminate\Contracts\Validation\Rule;
use MOIREI\MediaLibrary\Models\SharedContent;

class SharedContentTypes implements Rule
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
        return in_array($value, [SharedContent::ACCESS_TYPE_SECRET, SharedContent::ACCESS_TYPE_TOKEN]);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be one of SharedContent types.';
    }
}
