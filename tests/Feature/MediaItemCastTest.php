<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Attributes\MediaItemAttribute;
use MOIREI\MediaLibrary\Casts\AsMediaItem;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\MediaOptions;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('attributes', 'media-item');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    $attributes = [
        'id' => 1,
    ];
    $this->model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'image' => AsMediaItem::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };
});

it('should cast attribute', function () {
    expect($this->model->image)->toBeInstanceOf(MediaItemAttribute::class);
    expect($this->model->image->exists())->toBeFalse();
});

it('should upload and save file', function () {
    $disk = 'local';
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    /** @var File */
    $file = $this->model->image->upload($uploadedFile);

    expect($file)->toBeInstanceOf(File::class);
    expect($this->model->image->file())->toBeInstanceOf(File::class);
    Storage::disk($disk)->assertExists($file->uri());
});

it('should access/mutate file attributes from cast', function () {
    $disk = 'local';
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    /** @var File */
    $file = $this->model->image->upload($uploadedFile);

    expect($this->model->image->id)->toEqual($file->id);
    expect($this->model->image['id'])->toEqual($file->id);
    expect($this->model->image->name)->toEqual($file->name);
    expect($this->model->image['name'])->toEqual($file->name);
    $this->model->image->name = 'updated name [1]';
    expect($file->name)->toEqual('updated name [1]');
    $this->model->image['name'] = 'updated name [2]';
    expect($file->name)->toEqual('updated name [2]');
});

it('should validate upload from model-defined options', function () {
    $disk = 'local';
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);


    $attributes = [
        'id' => 1,
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'image' => AsMediaItem::class,
        ];
        public function save(array $options = [])
        {
            //
        }

        public function mediaConfig()
        {
            return [
                'image' => MediaOptions::make()->allow('pdf'),
            ];
        }
    };

    $model->image->upload($uploadedFile);
})->throws(\Illuminate\Validation\ValidationException::class);


it('should instantiate model with file from file ID attribute', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file = Upload::uploadFile($uploadedFile);

    $attributes = [
        'id' => 1,
        'image' => $file->id,
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'image' => AsMediaItem::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->image->file())->toBeInstanceOf(File::class);
});

it('should instantiate model with file from file FQFN attribute', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file = Upload::uploadFile($uploadedFile);

    $attributes = [
        'id' => 1,
        'image' => $file->fqfn,
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'image' => AsMediaItem::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->image->file())->toBeInstanceOf(File::class);
});

it('should upload file and associate via options', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file = Upload::uploadFile($uploadedFile);
    $originalPath = $file->uri();

    $attributes = [
        'id' => 1,
        'image' => $file->fqfn,
    ];

    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'image' => AsMediaItem::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    Storage::disk($disk)->assertExists($originalPath);

    $model->image = [
        'upload' => $uploadedFile,
    ];

    expect($model->image->file())->toBeInstanceOf(File::class);
    expect($model->image->file()->id)->not->toEqual($file->id);
    expect(File::find($file->id))->toBeFalsy();
    Storage::disk($disk)->assertMissing($originalPath);
});

it('should deleted uploaded file via options', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file = Upload::uploadFile($uploadedFile);
    $originalPath = $file->uri();

    $attributes = [
        'id' => 1,
        'image' => $file->fqfn,
    ];

    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'image' => AsMediaItem::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    Storage::disk($disk)->assertExists($originalPath);

    $model->image = [
        'delete' => true,
    ];

    expect($model->image->file())->toBeFalsy();
    expect(File::find($file->id))->toBeFalsy();
    Storage::disk($disk)->assertMissing($originalPath);
});
