<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\Attachment;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'upload');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
});

it('should create Upload', function () {
    $file = UploadedFile::fake()->image('avatar.jpg')->size(1200);
    $upload = new Upload($file);
    expect($upload->getAttribute('name'))->toEqual('avatar');
    expect($upload->getAttribute('filename'))->toEqual($file->name);
    expect($upload->getAttribute('extension'))->toEqual('jpg');
    expect($upload->getAttribute('type'))->toEqual('image');
    expect($upload->getAttribute('mime'))->toEqual('jpeg');
    expect($upload->getAttribute('original_size'))->toBeGreaterThanOrEqual(1.2e6);
});

it('should NOT validate upload', function () {
    $disk = 'avatars';
    Storage::fake($disk);

    $file = UploadedFile::fake()->image('avatar.jpg');
    $upload = new Upload($file);
    $upload->allow('png');

    expect($upload->validate())->toEqual(false);
});

it('should validate upload', function () {
    $disk = 'avatars';
    Storage::fake($disk);

    $file = UploadedFile::fake()->image('avatar.jpg');
    $upload = new Upload($file);
    $upload->allow('jpg');

    expect($upload->validate())->toEqual(true);
});

it('should throw when invalid upload', function () {
    $disk = 'avatars';
    Storage::fake($disk);

    $file = UploadedFile::fake()->image('avatar.jpg');
    $upload = new Upload($file);
    $upload->allow('png');

    $upload->validate(true);
})->throws(\Illuminate\Validation\ValidationException::class);

it('should upload and save file', function () {
    $disk = 'local';
    Storage::fake($disk);
    $file = UploadedFile::fake()->image('avatar.jpg');
    $storage = MediaStorage::create([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $upload = new Upload($file);

    /** @var File */
    $uploadedFile = $upload->storage($storage)->save();

    Storage::disk($disk)->assertExists($uploadedFile->uri());

    $uploadedFile->forceDelete();
    Storage::disk($disk)->assertMissing($uploadedFile->uri());
});

it('should upload and save attachment', function () {
    $disk = 'local';
    Storage::fake($disk);
    $file = UploadedFile::fake()->image('attachment.jpg');

    /** @var Attachment */
    $uploadedAttachment = Upload::attachment($file)->disk($disk)->save();

    Storage::disk($disk)->assertExists($uploadedAttachment->uri());

    $uploadedAttachment->forceDelete();
    Storage::disk($disk)->assertMissing($uploadedAttachment->uri());
});

it('should upload and save attachment [2]', function () {
    $disk = 'public';
    Storage::fake($disk);
    $file = UploadedFile::fake()->image('attachment.jpg');

    /** @var Attachment */
    $uploadedAttachment = Upload::uploadAttachment($file);

    Storage::disk($disk)->assertExists($uploadedAttachment->uri());
});

it('should upload file for model', function () {
    $disk = 'local';
    $file = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $model = new class([
        'id' => 1,
    ]) extends Model
    {
        protected $guarded = [];
        protected $casts = [
            'image' => AsMediaItem::class,
        ];
        public $exists = true;

        public function save(array $options = [])
        {
            //
        }
    };

    /** @var File */
    $uploadedFile = Upload::make($file)->for($model)->save();

    Storage::disk($disk)->assertExists($uploadedFile->uri());
    expect($uploadedFile->model)->toBeInstanceOf(Model::class);
    expect($uploadedFile->model->id)->toEqual(1);
});

it('should upload file with meta', function () {
    $disk = 'local';
    Storage::fake($disk);
    $file = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    /** @var File */
    $uploadedFile = Upload::make($file)->withMeta(['key' => 'value'])->save();

    Storage::disk($disk)->assertExists($uploadedFile->uri());
    expect($uploadedFile->meta)->toBeCollection();
    expect($uploadedFile->meta->toArray())->toHaveKey('key');
    expect($uploadedFile->meta->get('key'))->toEqual('value');
});

it('should upload file with multi meta and not overwrite', function () {
    $disk = 'local';
    $file = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    /** @var File */
    $uploadedFile = Upload::make($file)
        ->withMeta(['key1' => 'value-1'])
        ->withMeta(['key2' => 'value-2'])
        ->save();

    Storage::disk($disk)->assertExists($uploadedFile->uri());
    expect($uploadedFile->meta)->toBeCollection();
    expect($uploadedFile->meta->toArray())->toHaveKeys(['key1', 'key2']);
    expect($uploadedFile->meta->get('key1'))->toEqual('value-1');
});

// it('should upload from path', function () {
//     $disk = 'local';
//     Storage::fake($disk);

//     MediaStorage::createAndUse([
//         'name' => 'Avatars',
//         'disk' => $disk
//     ]);

//     $file1 = Upload::uploadFile(UploadedFile::fake()->image('avatar.jpg'));

//     $file2 = Upload::fromUrl($file1->uri())
//         ->disk($disk)
//         ->allow('jpg')
//         ->save();

//     expect($file2)->toBeInstanceOf(File::class);
//     Storage::disk($disk)->assertExists($file2->uri());
//     expect($file2->name)->toEqual($file1->name);
// });

// it('should upload SVG from url', function () {
//     $disk = 'local';
//     Storage::fake($disk);
//     $file = Upload::fromUrl('https://laravel.com/img/logomark.min.svg')
//         ->disk($disk)
//         ->allow('svg+xml')
//         ->save();
//     expect($file)->toBeInstanceOf(File::class);
//     Storage::disk($disk)->assertExists($file->uri());
// });

// it('should upload PNG from url', function () {

//     $disk = 'local';
//     Storage::fake($disk);
//     $file = Upload::fromUrl('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRIzpYJlpTEWXXBgwRpScU5luiJ-vE-DGOTCA&usqp=CAU')
//         ->disk($disk)
//         ->allow('png')
//         ->save();
//     expect($file)->toBeInstanceOf(File::class);
//     Storage::disk($disk)->assertExists($file->uri());
// });
