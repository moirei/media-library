<?php

namespace MOIREI\MediaLibrary\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Traits\UsesUuid;
use MOIREI\MediaLibrary\Upload;

/**
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $id
 * @property string $name
 * @property string $url
 * @property string $disk
 * @property bool $pending
 * @property string $alt
 * @property string $filename
 * @property string $attachable_type
 * @property string $attachable_id
 * @property Model $model
 * @property \Illuminate\Database\Eloquent\Collection $meta
 */
class Attachment extends Model
{
    use UsesUuid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'pending' => 'boolean',
        'meta' => AsCollection::class,
    ];

    /**
     * Delete from model
     *
     * @param UploadedFile $attachment
     * @param string $location
     * @param string $disk
     * @return Attachment
     */
    public static function store(
        UploadedFile $attachment,
        string $location = null,
        string $disk = null
    ): Attachment {
        return Upload::attachment($attachment)
            ->location($location)
            ->disk($disk)
            ->save();
    }

    /**
     * The admin or store team member who closed the enquiry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo(__FUNCTION__, 'attachable_type', 'attachable_id');
    }

    /**
     * Associate an Eloquent model with the attachment.
     *
     * @param Model $model
     * @return Attachment
     */
    public function attach(Model $model)
    {
        $this->update([
            'attachable_type' => $model->getMorphClass(),
            'attachable_id' => $model->getKey(),
        ]);

        return $this;
    }

    /**
     * Purge the attachment.
     *
     * @return Attachment
     */
    public function purge()
    {
        $this->delete();
        $path = $this->uri();
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }
        return $this;
    }

    /**
     * Persist the pending attachment.
     *
     * @return Attachment
     */
    public function persist()
    {
        $this->update(['pending' => false]);
        return $this;
    }

    /**
     * Prune the stale attachments from the system.
     *
     * @param Carbon|int $age
     * @return void
     */
    public static function pruneStale(Carbon | int $age = 1)
    {
        if (is_int($age)) {
            $age = now()->subDays($age);
        }

        $query = self::where('pending', true)
            ->where('created_at', '<=', $age)
            ->orderBy('id', 'desc');

        $query->chunk(100, function ($attachments) {
            $attachments->each->purge();
        });
    }

    /**
     * Discard pending attachment
     * @return void
     */
    public static function discardPending()
    {
        self::where('pending', true)
            ->get()
            ->each
            ->purge();
    }

    /**
     * Get an attachment via id or url.
     *
     * @param string $id
     * @return Attachment|null
     */
    public static function get(string $id): Attachment|null
    {
        if (Api::isUuid($id)) {
            $attachment = self::find($id);
        } else {
            $attachment = self::where('url', $id)->first();
        }
        return $attachment;
    }

    /**
     * Get the attachment file uri
     *
     * @return string
     */
    public function uri(): string
    {
        return Api::joinPaths(
            config('media-library.folder', 'media'),
            config('media-library.uploads.attachments.location', 'attachments'),
            $this->filename,
        );
    }
}
