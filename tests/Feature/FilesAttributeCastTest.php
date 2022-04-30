<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Attributes\FilesAttribute;
use MOIREI\MediaLibrary\Casts\AsMediaFiles;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('attributes', 'files-attribute');

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
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };
});

it('should cast attribute', function () {
    expect($this->model->files)->toBeInstanceOf(FilesAttribute::class);
});

it('should upload associate files', function () {
    $disk = 'local';
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    /** @var File */
    $file = $this->model->files->upload($uploadedFile);

    expect($file)->toBeInstanceOf(File::class);
    expect($this->model->files->first())->toBeInstanceOf(File::class);
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
            'location' => 'files',
        ],
        [
            'filename' => 'avatar-2.png',
            'private' => true,
            'location' => 'files',
        ],
        [
            'filename' => 'avatar-3.jpg',
            'private' => false,
            'location' => 'files/products',
        ],
    ];

    $uploadedFiles = [];

    foreach ($files as $options) {
        $uploadedFiles[] = UploadedFile::fake()->image($options['filename']);
    }

    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $initial = $this->model->files->upload($uploadedFile);
    $newUploads = $this->model->files->uploadAndSet($uploadedFiles);

    expect($initial)->toBeInstanceOf(File::class);
    expect($newUploads)->toHaveCount(3);
    expect($this->model->files->count())->toEqual(3);
    expect(File::count())->toEqual(4);
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
        'files' => [$file->id],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->files)->toBeCollection();
    expect($model->files->first())->toBeInstanceOf(File::class);
    expect($model->files->count())->toEqual(1);
    expect($model->files->first()->id)->toEqual($file->id);
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
        'files' => [$file],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->files)->toBeCollection();
    expect($model->files->first())->toBeInstanceOf(File::class);
    expect($model->files->first()->id)->toEqual($file->id);
});

it('should add file to casted attribute', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file1 = Upload::uploadFile($uploadedFile);
    $file2 = Upload::uploadFile($uploadedFile);

    $attributes = [
        'id' => 1,
        'files' => [],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    // add instance or with id
    $model->files->add($file1, $file2->id);

    expect($model->files)->toBeCollection();
});

it('should remove file from casted attribute', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file1 = Upload::uploadFile($uploadedFile);
    $file2 = Upload::uploadFile($uploadedFile);

    $attributes = [
        'id' => 1,
        'files' => [$file1, $file2],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    // add instance or with id
    $model->files->remove($file1, $file2->id);

    expect($model->files->isEmpty())->toBeTrue();
});

it('should set files to casted attribute', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file1 = Upload::uploadFile($uploadedFile);
    $file2 = Upload::uploadFile($uploadedFile);

    $attributes = [
        'id' => 1,
        'files' => [],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    // add instance or with id
    $model->files->set([$file1, $file2->id]);

    expect($model->files->count())->toEqual(2);
});

it('should set files to casted attribute via options', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file1 = Upload::uploadFile($uploadedFile);
    $file2 = Upload::uploadFile($uploadedFile);

    $attributes = [
        'id' => 1,
        'files' => [],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    $model->files = [
        'set' => [$file1, $file2->id]
    ];

    expect($model->files->count())->toEqual(2);
});

it('should attach more files to casted attribute via options', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file1 = Upload::uploadFile($uploadedFile);
    $file2 = Upload::uploadFile($uploadedFile);

    $attributes = [
        'id' => 1,
        'files' => [$file1],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->files->count())->toEqual(1);

    $model->files = [
        'attach' => [$file1, $file2->id]
    ];

    expect($model->files->count())->toEqual(2);
});


it('should detach files from casted attribute via options', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $file1 = Upload::uploadFile($uploadedFile);
    $file2 = Upload::uploadFile($uploadedFile);
    $file3 = Upload::uploadFile($uploadedFile);

    $attributes = [
        'id' => 1,
        'files' => [$file1, $file2],
    ];
    $model = new class($attributes) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    expect($model->files->count())->toEqual(2);

    $model->files = [
        'detach' => [$file1, $file3->id]
    ];

    expect($model->files->count())->toEqual(1);
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
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    $model->files = [
        'upload' => $uploadedFile,
    ];

    expect($model->files->count())->toEqual(1);
    expect($model->files->first())->toBeInstanceOf(File::class);
});

it('should upload multiple files and associate via options', function () {
    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile1 = UploadedFile::fake()->image('avatar.jpg');
    $uploadedFile2 = UploadedFile::fake()->image('avatar.jpg');
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
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    $model->files = [
        'upload' => [$uploadedFile1, $uploadedFile2],
    ];

    expect($model->files->count())->toEqual(2);
    expect($model->files->first())->toBeInstanceOf(File::class);
});


it('should upload file with location and associate via options', function () {
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
            'files' => AsMediaFiles::class,
        ];
        public function save(array $options = [])
        {
            //
        }
    };

    $model->files = [
        'upload' => [
            'files' => [$uploadedFile],
            'location' => 'products',
        ],
    ];

    expect($model->files->count())->toEqual(1);
    expect(in_array($model->files->first()->id, $storage->files->map->id->toArray()))->toBeTrue();
    expect($storage->browse('products', ['filesOnly' => true]))->toHaveCount(1);
});
