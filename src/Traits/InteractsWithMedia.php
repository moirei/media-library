<?php

namespace MOIREI\MediaLibrary\Traits;

use ArrayAccess;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\Attachment;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Casts\MediaCast;

/**
 * @property \Illuminate\Support\Collection<File> $media
 */
trait InteractsWithMedia
{
    /**
     * Queued shared files pending association with model once created.
     * @var array
     */
    protected array $queuedLinkedFiles = [];

    /**
     * Queued owned files pending association with model once created.
     * @var array
     */
    protected array $queuedOwnedFiles = [];

    /**
     * Get the casts array.
     *
     * @return array
     */
    abstract public function getCasts();

    /**
     * Parse the given caster class, removing any arguments.
     *
     * @param  string  $class
     * @return string
     */
    abstract protected function parseCasterClass($class);

    /**
     * Get media casts array.
     *
     * @return array
     */
    public function getMediaCasts()
    {
        $casts = $this->getCasts();
        $mediaClasses = [];

        foreach ($casts as $key => $cast) {
            $castType = $this->parseCasterClass($cast);
            if (!in_array($castType, static::$primitiveCastTypes)) {
                if (class_exists($castType) && is_subclass_of($castType, MediaCast::class)) {
                    $mediaClasses[$key] = $castType;
                }
            }
        }

        return $mediaClasses;
    }

    /**
     * get rich text fields
     *
     * @return array
     */
    public function richTextFields()
    {
        return $this->richTextFields;
    }

    public static function bootInteractsWithMedia()
    {
        static::created(function (Model $model) {
            // handle attachments
            if (property_exists($model, 'richTextFields')) {
                Api::persistAttachments($model, true);
            }

            if (count($model->queuedLinkedFiles)) {
                $model->attachFiles($model->queuedLinkedFiles);
                $model->queuedLinkedFiles = [];
            }
            if (count($model->queuedOwnedFiles)) {
                $model->attachFiles($model->queuedOwnedFiles);
                $model->queuedOwnedFiles = [];
            }
        });

        static::updating(function (Model $model) {
            // handle attachments
            if (property_exists($model, 'richTextFields')) {
                Api::persistAttachments($model);
            }
        });

        static::deleting(function ($model) {
            $model->attachments->each->purge();
            // $model->media()->detach();
            $model->attributesMedia()->delete();
        });
    }

    /**
     * All media files associated with model
     * @return MorphToMany
     */
    public function media(): MorphToMany
    {
        return $this->morphToMany(config('media-library.models.file'), 'fileable');
    }

    /**
     * All media files belonging to model
     * @return MorphMany
     */
    public function attributesMedia(): MorphMany
    {
        return $this->morphMany(
            config('media-library.models.file'),
            'model',
            'model_type',
            'model_id'
        );
    }

    /**
     * All media attachments
     * @return MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(
            Attachment::class,
            'attachable',
            'attachable_type',
            'attachable_id'
        );
    }

    /**
     * Get media of type
     * @param string $type
     */
    public function getMediaType(string $type)
    {
        return $this->media()->ofType($type)->get();
    }

    /**
     * Set the media files
     * @param string|array|ArrayAccess|File $files
     */
    public function setMediaAttribute(string | array | ArrayAccess | File $files)
    {
        if (!$this->exists) {
            $this->queuedLinkedFiles = array_merge($this->queuedLinkedFiles, is_array($files) ? $files : [$files]);
            return;
        }

        $this->syncMedia($files);
    }

    /**
     * Attach media files and delete all existing.
     * @param array|ArrayAccess $files
     */
    public function setOwnFiles(array | ArrayAccess $files): static
    {
        if ($this->exists) {
            $existingFiles = $this->media()->get();

            $className = config('media-library.models.file');
            foreach ($files as $file) {
                if (is_string($file)) {
                    $file = $className::find($file);
                }
                if ($file) {
                    /** @var File $file */
                    $file->update([
                        'model_type' => $this->getMorphClass(),
                        'model_id' => $this->getKey(),
                    ]);
                }
            }

            // remove all existing files after
            /** @var File */
            foreach ($existingFiles as $file) {
                $file->forceDelete();
            }
        } else {
            $this->queuedOwnedFiles = array_merge($this->queuedOwnedFiles, $files);
        }

        return $this;
    }

    /**
     * Attach media one or more owned files
     * @param array|ArrayAccess $files
     */
    public function setOwnFile(string | File | array $file)
    {
        return $this->setOwnFiles(is_array($file) ? $file : [$file]);
    }

    /**
     * Sync media with files
     * @param array|ArrayAccess $files
     */
    public function syncMedia(array | ArrayAccess $files): static
    {
        $className = config('media-library.models.file');

        $files = collect($className::find($files));

        $key  = Api::fileClassKey();
        $this->media()->sync($files->pluck($key)->toArray());

        return $this;
    }

    /**
     * Sync new files and remove all existing.
     * @param array|ArrayAccess $files
     */
    public function syncFiles(array | ArrayAccess $files): static
    {
        $key  = Api::fileClassKey();
        $ids = collect($files)->map(fn ($file) => is_string($file) ? $file : Arr::get($file, $key));

        $this->media()->sync($ids);

        return $this;
    }

    /**
     * Attach media files and keep all existing.
     * @param array|ArrayAccess $files
     */
    public function attachFiles(array | ArrayAccess $files): static
    {
        $key  = Api::fileClassKey();
        $ids = collect($files)->map(fn ($file) => is_string($file) ? $file : Arr::get($file, $key));

        $this->media()->syncWithoutDetaching($ids);

        return $this;
    }

    /**
     * Attach media one or more file
     * @param array|ArrayAccess $files
     */
    public function attachFile(string | File | array $file)
    {
        return $this->attachFiles(is_array($file) ? $file : [$file]);
    }

    /**
     * Detach files
     * @param array|ArrayAccess $files
     */
    public function detachFiles(array | ArrayAccess $files): static
    {
        $key  = Api::fileClassKey();
        $ids = collect($files)->map(fn ($file) => is_string($file) ? $file : Arr::get($file, $key));

        $this->media()->detach($ids);

        return $this;
    }

    /**
     * Detach one or more files
     * @param string|File|array $files
     */
    public function detachFile(string | File | array $file): static
    {
        return $this->detachFiles(is_array($file) ? $file : [$file]);
    }

    /**
     * Detach all media files
     */
    public function clearMedia()
    {
        return $this->media()->detach();
    }
}
