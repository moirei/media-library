<?php

namespace MOIREI\MediaLibrary;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Image;
use MOIREI\MediaLibrary\Exceptions\FileValidationException;
use MOIREI\MediaLibrary\Models\Attachment;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\MediaStorage;

class Upload
{
    /**
     * The uploadable object.
     *
     * @var UploadedFile
     */
    protected UploadedFile $upload;

    /**
     * The upload folder.
     *
     * @var Folder|string
     */
    protected Folder|string $folder;

    /**
     * The upload storage.
     *
     * @var MediaStorage|Closure|string
     */
    protected MediaStorage|Closure|string $storage;

    /**
     * Media upload options.
     *
     * @var MediaOptions
     */
    protected MediaOptions $options;

    /**
     * Indicates if upload is an attachment or file.
     *
     * @var bool
     */
    protected bool $attachment = false;

    /**
     * Applied a file upload to this model.
     *
     * @var Model
     */
    protected ?Model $model = null;

    /**
     * MediaStorage disk option for attachment uploads.
     *
     * @var string
     */
    protected string $disk;

    /**
     * The upload file attributes.
     *
     * @var array
     */
    protected array $attributes = [];

    public function __construct(UploadedFile $upload, MediaOptions $options = null)
    {
        $this->upload = $upload;
        $this->options = $options ?: new MediaOptions;
        $clientOriginalName = $upload->getClientOriginalName();
        $mimetype = strtolower($upload->getMimeType());
        $extension = $upload->getClientOriginalExtension();

        $this->attributes['name'] = pathinfo($clientOriginalName, PATHINFO_FILENAME);
        $this->attributes['filename'] = $clientOriginalName;
        $this->attributes['extension'] = strtolower($extension);
        $this->attributes['type'] = Api::getFileType($mimetype, $extension);
        $this->attributes['mimetype'] = $mimetype;
        $this->attributes['mime'] = Api::getSubType($mimetype);
        $this->attributes['original_size'] = $upload->getSize();
    }

    /**
     * Create a file upload
     *
     * @param UploadedFile $upload
     * @param MediaOptions $options
     * @return self
     */
    public static function make(UploadedFile $upload, MediaOptions $options = null): self
    {
        return new static($upload, $options);
    }

    /**
     * Create an attachment upload
     *
     * @param UploadedFile $upload
     * @param MediaOptions $options
     * @return self
     */
    public static function attachment(UploadedFile $upload, MediaOptions $options = null): self
    {
        return static::make($upload, $options)->asAttachment();
    }

    /**
     * Create and upload file
     *
     * @param UploadedFile $upload
     * @param MediaOptions $options
     * @return File
     */
    public static function uploadFile(UploadedFile $upload, MediaOptions $options = null): File
    {
        return static::make($upload, $options)->save();
    }

    /**
     * Create and upload file
     *
     * @param UploadedFile $upload
     * @param MediaOptions $options
     * @return Attachment
     */
    public static function uploadAttachment(UploadedFile $upload, MediaOptions $options = null): Attachment
    {
        return static::attachment($upload, $options)->save();
    }

    /**
     * Upload file from a public url or local path
     *
     * @param string $url
     * @param string $name
     * @return Upload
     */
    public static function fromUrl(string $url, MediaOptions $options = null): Upload
    {
        if (Str::startsWith($url, ['http://', 'https://'])) {
            if (!$stream = @fopen($url, 'r')) {
                throw FileValidationException::unreachableUrl($url);
            }
            $temp_file = tempnam(sys_get_temp_dir(), 'media-library');
            file_put_contents($temp_file, $stream);
            // file_put_contents($temp_file, file_get_contents($url));
        } else {
            // local path
            $temp_file = tempnam(sys_get_temp_dir(), 'media-library');
            file_put_contents($temp_file, file_get_contents($url));
        }

        $filename = basename(parse_url($url, PHP_URL_PATH));
        $filename = urldecode($filename);
        $mimetype = mime_content_type($temp_file);
        $uploadable = new UploadedFile($temp_file, $filename, $mimetype);

        $upload = new self($uploadable, $options);
        $upload->name(pathinfo($filename, PATHINFO_FILENAME));

        return $upload;
    }

    /**
     * Get an attribute
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        return Arr::get($this->attributes, $name, $default);
    }

    /**
     * Get all attributes
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Clean filename; replace spaces and special characters
     *
     * @param bool $special_characters allow special characters
     * @return self
     */
    public function cleanFilename(bool $special_characters = false)
    {
        $this->attributes['filename'] = Str::slug($this->attributes['filename'], config('media-library.clean_file_name.replace_spaces', '-'));
        if (!$special_characters) {
            $extension = $this->attributes['extension'];
            $this->attributes['filename'] = preg_replace('/[^A-Za-z0-9\-]/', '', pathinfo($this->attributes['filename'], PATHINFO_FILENAME)) . ".$extension";
        }

        return $this;
    }

    /**
     * Set file folder
     *
     * @param Folder|string $folder
     * @return self
     */
    public function folder($folder)
    {
        $this->options->folder($folder);
        return $this;
    }

    /**
     * Set file location
     *
     * @param string $location
     * @return self
     */
    public function location(string $location)
    {
        $this->options->location($location);
        return $this;
    }

    /**
     * Set upload file name
     *
     * @param string $name
     * @return self
     */
    public function name(string $name)
    {
        $this->attributes['name'] = $name;
        return $this;
    }

    /**
     * Set upload file description
     *
     * @param string $description
     * @return self
     */
    public function description(string $description)
    {
        $this->attributes['description'] = $description;
        return $this;
    }

    /**
     * Set upload file privacy
     *
     * @param string $private
     * @return self
     */
    public function private(bool $private = true)
    {
        $this->options->private($private);
        return $this;
    }

    /**
     * Set upload file extension
     *
     * @param string $extension
     * @return self
     */
    public function extension(string $extension)
    {
        $this->attributes['extension'] = strtolower($extension);
        return $this;
    }

    /**
     * Set meta info on uploaded file
     *
     * @param Collection|array $meta
     * @return self
     */
    public function withMeta(Collection|array $meta)
    {
        $this->attributes['meta'] = collect($this->getAttribute('meta', []))->merge(collect($meta));
        return $this;
    }

    /**
     * Set upload storage
     *
     * @param MediaStorage|Closure|string $storage
     * @return self
     */
    public function storage($storage)
    {
        $this->options->storage($storage);
        return $this;
    }

    /**
     * Set max site
     *
     * @param int|null $size, bool == throw error
     * @return self
     */
    public function maxSize(int | bool $size = null)
    {
        if (is_bool($size)) {
            $size = config('media-library.uploads.max_size.*');
        } else {
            $size = config('media-library.uploads.max_size.' . $this->type);
        }

        $this->options->maxSize($size);

        return $this;
    }

    /**
     * Valid mime types
     *
     * @param $types
     * @return self
     */
    public function allow(...$types)
    {
        $this->options->mime(...$types);
        return $this;
    }

    /**
     * Valid mime types
     *
     * @param $types
     * @return self
     */
    public function mimes(...$types)
    {
        $this->options->mime(...$types);
        return $this;
    }

    /**
     * Crop image
     *
     * @param int $width
     * @param int $height
     * @param int[] $coordinates [x, y] displacement coordinates
     * @return self
     */
    public function crop(int $width, int $height, array $coordinates = null)
    {
        $this->options->crop($width, $height, $coordinates);
        return $this;
    }

    /**
     * Resize image
     *
     * @param int $width
     * @param int $height
     * @param bool $upSize
     * @param bool $aspectRatio
     * @return self
     */
    public function resize(
        int $width,
        int $height,
        bool $upSize = false,
        bool $aspectRatio = false
    ) {
        $this->options->resize($width, $height, $upSize, $aspectRatio);
        return $this;
    }

    /**
     * Convert upload image to an alternate type.
     *
     * @param string $type
     * @return self
     */
    public function convertTo(string $type)
    {
        $this->options->convertTo($type);
        return $this;
    }

    /**
     * Apply filters
     *
     * @param string[] $filters
     * @return self
     */
    public function apply(array $filters)
    {
        $this->options->apply($filters);
        return $this;
    }

    /**
     * Set attachment storage disk
     *
     * @param string $disk
     * @return self
     */
    public function disk(string $disk)
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Make file upload for model
     *
     * @param Model $model
     * @return self
     */
    public function for(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Make an attachment upload
     *
     * @param bool $attachment
     * @return self
     */
    public function asAttachment(bool $attachment = true)
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * Check if file is an image
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->attributes['type'] === 'image';
    }

    /**
     * Save image
     *
     * @return Model
     */
    public function save(): Model
    {
        $this->validate(true);
        if ($this->attachment) return $this->saveAsAttachment();
        return $this->saveAsFile();
    }

    /**
     * Validate the upload
     *
     * @param bool $assert
     * @throws \Illuminate\Validation\ValidationException
     * @return bool
     */
    public function validate(bool $assert = false)
    {
        $validator = Validator::make([
            'upload' => $this->upload,
        ], [
            'upload' => [
                'image',
                'mimes:' . implode(',', $this->options->get('types')),
                'max:' . $this->options->get('maxSize'),
            ],
        ]);

        if ($assert) {
            $validator->validate();
        }

        return !$validator->fails();
    }

    /**
     * Save the upload as file
     *
     * @return Model
     */
    protected function saveAsFile(): Model
    {
        /** @var MediaStorage */
        $storage = $this->options->get('storage');

        /** @var Folder */
        $folder = $this->options->get('folder');

        $file = $storage->newFile(array_merge($this->attributes, [
            'id' => $id = Str::uuid()->toString(),
            'fqfn' => $id . '-' . Str::slug($this->getAttribute('filename')),
            'private' => $private = $this->options->get('private'),
            'location' => $this->options->get('location'),
        ]));

        if ($this->model) {
            if (Api::interactsWithMedia($this->model)) {
                $this->model->setOwnFile($file);
            } else {
                // remove any existing files
                $fileClass = (string)config('media-library.models.file');
                $modelWhere = [
                    'model_type' => $this->model->getMorphClass(),
                    'model_id' => $this->model->getKey(),
                ];
                $files = $fileClass::where($modelWhere)->get();

                /** @var File */
                foreach ($files as $f) {
                    $f->forceDelete();
                }

                $file->model()->associate($this->model);
            }
        }

        if ($folder) {
            $file->folder()->associate($folder);
        }

        $path = $file->path();
        $attributes = [];

        // Ensure directory exists
        Storage::disk($storage->disk)->makeDirectory($path);

        if ($this->isImage()) {
            $image = $this->getUploadImage();
            $content = (string)$image->stream(null, config('media-library.uploads.quality'));
            $attributes['size'] = strlen($content);
            [$responsive, $extraSize] = $this->saveResponsiveSizes(
                $image,
                $path,
                $storage->disk,
            );
            $attributes['total_size'] = $attributes['size'] + $extraSize;
            $attributes['responsive'] = $responsive;
        } else {
            $content = $this->upload->get();
            $attributes['size'] = strlen($content);
            $attributes['total_size'] = $attributes['size'];
        }

        Storage::disk($storage->disk)->put(
            Api::joinPaths($path, $this->getAttribute('filename')),
            $content,
            Api::visibility($private),
        );

        $file->fill($attributes);
        $file->save();

        return $file;
    }

    /**
     * Save the upload as attachment
     *
     * @return Model
     */
    protected function saveAsAttachment(): Model
    {
        $filename = $this->getAttribute('filename');
        $disk = isset($this->disk) ? $this->disk : config('media-library.uploads.attachments.disk', 'local');
        $private = $this->options->get('private');

        /** @var Attachment */
        $attachment = Attachment::make([
            'filename' =>  Str::uuid()->toString() . '-' . $filename, // prepend with uuid for uniqueness
            'disk' => $disk,
            'alt' => pathinfo($filename, PATHINFO_FILENAME),
            'meta' => $this->getAttribute('meta'),
        ]);
        $path = $attachment->uri();

        if ($this->isImage()) {
            $image = $this->getUploadImage();
            $imageTo = $this->options->get('imageTo');
            if (in_array($imageTo, [
                'jpg', 'png', 'gif',
                'tif', 'bmp', 'ico', 'psd',
                'webp', 'data-url'
            ])) {
                $content = (string)$image->encode($imageTo, config('media-library.uploads.quality'));
            } else {
                $content = (string)$image->stream(null, config('media-library.uploads.quality'));
            }
        } else {
            $content = $this->upload->get();
        }

        Storage::disk($disk)->put($path, $content, Api::visibility($private));
        $attachment->url = Storage::disk($disk)->url($path);

        $attachment->save();

        return $attachment;
    }

    /**
     * Get upload image
     *
     * @return Image
     */
    protected function getUploadImage(): Image
    {
        $manager = new ImageManager(['driver' => config('media-library.driver', 'gd')]);
        $image = $manager->make($this->upload);

        $this->applyResize($image);
        $this->applyCrop($image);
        $this->applyFilters($image);

        return $image;
    }

    /**
     * Apply resize
     */
    protected function applyResize(Image $image)
    {
        $resize = $this->options->get('resize');
        if ($resize) {
            [$width, $height, $upSize, $aspectRatio] = $resize;
        } elseif ($this->attachment) {
            $width  = config('media-library.uploads.attachments.resize.0');
            $height = config('media-library.uploads.attachments.resize.1');
            $upSize = config('media-library.uploads.attachments.resize.2');
            $aspectRatio = config('media-library.uploads.attachments.resize.3');
        } else {
            $width  = config('media-library.uploads.images.resize.0');
            $height = config('media-library.uploads.images.resize.1');
            $upSize = config('media-library.uploads.images.resize.2');
            $aspectRatio = config('media-library.uploads.images.resize.3');
        }

        $image->resize($width, $height, function ($constraint) use ($width, $height, $upSize, $aspectRatio) {
            if (!$width or !$height or $aspectRatio) $constraint->aspectRatio();
            if (!$upSize) $constraint->upSize();
        });
    }

    /**
     * Apply filters
     */
    protected function applyFilters(Image $image)
    {
        $filters = $this->options->get('filters');
        if (!$filters) {
            if ($this->attachment) {
                $filters = config('media-library.uploads.attachments.filters', []);
            } else {
                $filters = config('media-library.uploads.images.filters', []);
            }
        }
        foreach ($filters as $filter => $args) {
            if (is_string($args)) {
                $filter = $args;
                $args = [];
            }
            $image->filter(new $filter(...$args));
        }
    }

    /**
     * Crop image
     */
    protected function applyCrop(Image $image)
    {
        $crop = $this->options->get('crop');
        if (!$crop) {
            if ($this->attachment) {
                $crop = config('media-library.uploads.attachments.crop');
            } else {
                $crop = config('media-library.uploads.images.crop');
            }
        }
        if ($crop) {
            $image->crop(...$crop);
        }
    }

    /**
     * @param Image $image
     * @param string $path
     * @param string $disk
     */
    protected function saveResponsiveSizes(Image $image, string $path, string $disk)
    {
        $responsive = [];
        $size = 0;

        if ($this->isImage() && !config('media-library.uploads.images.responsive.disabled', false)) {
            $originalName = Str::slug($this->getAttribute('name'));
            $extension = $this->getAttribute('extension');
            $quality = config('media-library.uploads.quality');
            $image->backup();

            foreach (config('media-library.uploads.images.responsive.sizes') as $key => $values) {

                $name = "$originalName-$key";
                $width  = data_get($values, '0');
                $height  = data_get($values, '1');
                $upSize  = data_get($values, '2');

                array_push($responsive, [
                    'name' => $name,
                    'filename' => "$name.$extension",
                    'key' => $key,
                    'width' => $width,
                    'height' => $height,
                ]);

                $fn = ($width and $height) ? 'fit' : 'resize';
                $data = (string)$image->$fn($width, $height, function ($constraint) use ($width, $height, $upSize) {
                    if (!$width or !$height) $constraint->aspectRatio();
                    if (!$upSize) $constraint->upsize();
                })->stream(null, $quality);

                $size += strlen($data);

                Storage::disk($disk)->put(Api::joinPaths($path, "$name.$extension"), $data);
                $image->reset();
            }
        }

        return [
            $responsive,
            $size,
        ];
    }
}
