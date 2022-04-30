<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Attributes\MediaItemsAttribute;
use MOIREI\MediaLibrary\Casts\AsMediaItems;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\MediaOptions;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('attributes', 'media-items');

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
            'images' => AsMediaItems::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };
});

it('should cast attribute', function () {
    expect($this->model->images)->toBeInstanceOf(MediaItemsAttribute::class);
});

it('should upload and save model files', function () {
    $disk = 'local';
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    /** @var File */
    $file = $this->model->images->upload($uploadedFile);

    expect($file)->toBeInstanceOf(File::class);
    expect($this->model->images->first())->toBeInstanceOf(File::class);
    Storage::disk($disk)->assertExists($file->uri());
});

it('expects upload to overrite existing', function () {
    $disk = 'local';
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $files = [
        [
            'filename' => 'avatar-1.jpg',
            'private' => false,
            'location' => 'images',
        ],
        [
            'filename' => 'avatar-2.png',
            'private' => true,
            'location' => 'images',
        ],
        [
            'filename' => 'avatar-3.jpg',
            'private' => false,
            'location' => 'images/products',
        ],
    ];

    $uploadedFiles = [];

    foreach ($files as $options) {
        $uploadedFiles[] = UploadedFile::fake()->image($options['filename']);
    }

    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $initial = $this->model->images->upload($uploadedFile);
    $newUploads = $this->model->images->uploadAndSet($uploadedFiles);

    expect($initial)->toBeInstanceOf(File::class);
    expect($newUploads)->toHaveCount(3);
    expect($this->model->images->count())->toEqual(3);
    expect(File::count())->toEqual(3);
    expect(collect($newUploads)->map->id)->not->toContain($initial->id);
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
            'images' => AsMediaItems::class,
        ];
        public function save(array $options = [])
        {
            //
        }

        public function mediaConfig()
        {
            return [
                'images' => MediaOptions::make()->allow('pdf'),
            ];
        }
    };

    $model->images->upload($uploadedFile);
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
        'images' => [$file->id],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'images' => AsMediaItems::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->images)->toBeCollection();
    expect($model->images->first())->toBeInstanceOf(File::class);
    expect($model->images->count())->toEqual(1);
    expect($model->images->first()->id)->toEqual($file->id);
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
        'images' => [$file],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'images' => AsMediaItems::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->images)->toBeCollection();
    expect($model->images->first())->toBeInstanceOf(File::class);
    expect($model->images->first()->id)->toEqual($file->id);
});

it('should upload files via options', function () {
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
        'images' => [$file->fqfn],
    ];

    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'images' => AsMediaItems::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    $model->images = [
        'upload' => [$uploadedFile],
    ];

    expect($model->images->count())->toEqual(2);
    expect($model->images->first())->toBeInstanceOf(File::class);
});


it('should delete files via options', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file1 = Upload::uploadFile($uploadedFile);
    $file2 = Upload::uploadFile($uploadedFile);
    $originalPath1 = $file1->uri();

    $attributes = [
        'id' => 1,
        'images' => [$file1, $file2],
    ];

    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'images' => AsMediaItems::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    Storage::disk($disk)->assertExists($originalPath1);
    expect($model->images->count())->toEqual(2);

    $model->images = [
        'delete' => [$file1],
    ];

    expect($model->images->count())->toEqual(1);
    Storage::disk($disk)->assertMissing($originalPath1);
});
