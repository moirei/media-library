<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Casts\AsMediaFile;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('attributes', 'file-attribute');

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
            'file' => AsMediaFile::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };
});

it('expects empty casted attribute to be null', function () {
    expect($this->model->file)->toBeNull();
});

it('should associate file', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file = Upload::uploadFile($uploadedFile);
    $this->model->file = $file;

    expect($this->model->file)->toBeInstanceOf(File::class);
    expect($attributes = $this->model->getAttributes())->toHaveKey('file');
    expect($attributes['file'])->toEqual($file->id);
});

it('should access/mutate file attributes from cast', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file = Upload::uploadFile($uploadedFile);
    $this->model->file = $file;

    $this->model->file->name = 'updated name [1]';
    expect($file->name)->toEqual('updated name [1]');
    $this->model->file['name'] = 'updated name [2]';
    expect($file->name)->toEqual('updated name [2]');
});

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
        'file' => $file->id,
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'file' => AsMediaFile::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->file)->toBeInstanceOf(File::class);
});

it('should instantiate model with file from file instance attribute', function () {
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
        'file' => $file,
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'file' => AsMediaFile::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->file)->toBeInstanceOf(File::class);
});


it('should set file field using options', function () {
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
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'file' => AsMediaFile::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    $model->file = [
        'set' => $file->id,
    ];

    expect($model->file)->toBeInstanceOf(File::class);
    expect($model->file->id)->toEqual($file->id);
});

it('should remove associated file via options', function () {
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
        'file' => $file,
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'file' => AsMediaFile::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->file)->toBeInstanceOf(File::class);
    expect($model->file->id)->toEqual($file->id);

    $model->file = [
        'detach' => true,
    ];

    expect($model->file)->toBeNull();
});


it('should upload file and associate via options', function () {
    $disk = 'local';
    Storage::fake($disk);
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
            'file' => AsMediaFile::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    $model->file = [
        'upload' => $uploadedFile,
    ];

    expect($model->file)->toBeInstanceOf(File::class);
});

it('should upload file to location and associate via options', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');

    $storage = MediaStorage::createAndUse([
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
            'file' => AsMediaFile::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    $model->file = [
        'upload' => [
            'file' => $uploadedFile,
            'location' => 'products',
        ],
    ];;

    expect($model->file)->toBeInstanceOf(File::class);
    expect(in_array($model->file->id, $storage->files->map->id->toArray()))->toBeTrue();
});
