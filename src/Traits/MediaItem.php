<?php

namespace MOIREI\MediaLibrary\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Models\SharedContent;

trait MediaItem
{
    abstract public function path(): string;
    abstract public function publicUrl(Carbon | int | null $ttl = null): string|null;
    abstract public function url(Carbon | int | null $ttl = null): string|null;
    abstract public function protectedUrl(): string|null;

    /**
     * Share this file or folder
     *
     * @return SharedContent
     */
    public function share(): SharedContent
    {
        return SharedContent::make($this);
    }

    /**
     * Get all of the file's shares.
     */
    public function shares(): MorphMany
    {
        return $this->morphMany(
            SharedContent::class,
            'shareable',
            'shareable_type',
            'shareable_id'
        );
    }

    public function getPublicUrlAttribute()
    {
        return $this->publicUrl();
    }

    public function getUrlAttribute()
    {
        return $this->url();
    }

    public function getProtectedUrlAttribute()
    {
        return $this->protectedUrl();
    }

    public function getPathAttribute()
    {
        return $this->path();
    }

    public function getIsFolderAttribute()
    {
        return Api::isFolder($this);
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->publicUrl();
    }
}
