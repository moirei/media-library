<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\MediaStorage;
use Illuminate\Http\UploadedFile;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'folder');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
});

it('should create folder', function () {
    /** @var MediaStorage */
    $storage = MediaStorage::create(['name' => 'Products']);
    $folder = $storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
        'private' => true,
    ]);

    expect($folder)->toBeInstanceOf(Folder::class);
    expect(Folder::query()->count())->toEqual(2);
    expect($folder->name)->toEqual('Images');
    expect($folder->location)->toEqual('accessories');
    expect($folder->private)->toBeTrue();
});

it('should create folder from assert', function () {
    /** @var MediaStorage */
    $storage = MediaStorage::create(['name' => 'Products']);
    $folder = $storage->assertFolder('Images');

    expect($folder)->toBeInstanceOf(Folder::class);
    expect(Folder::query()->count())->toEqual(1);
});

it('should create multiple folders from assert', function () {
    /** @var MediaStorage */
    $storage = MediaStorage::create(['name' => 'Products']);
    $folder = $storage->assertFolder('Images/Products');

    expect($folder)->toBeInstanceOf(Folder::class);
    expect(Folder::query()->count())->toEqual(2);
});

it('should create multiple folders from nested asserts', function () {
    /** @var MediaStorage */
    $storage = MediaStorage::create(['name' => 'Products']);

    $storage->assertFolder('Images/Products');
    $storage->assertFolder('Images/Products/More');
    $folder = $storage->assertFolder('Images/Products/More/AndMore');

    expect($folder)->toBeInstanceOf(Folder::class);
    expect($folder->name)->toEqual('AndMore');
    expect($folder->location)->toEqual('Images/Products/More');
    expect(Folder::query()->count())->toEqual(4);
});

it('should zip folder', function () {
    $disk = 'local';
    Storage::fake($disk);

    MediaStorage::createAndUse([
        'name' => 'Uploads',
        'disk' => 'local',
    ]);

    Upload::make(UploadedFile::fake()->image('avatar-1.jpg'))->folder('Test folder')->save();
    Upload::make(UploadedFile::fake()->image('avatar-2.jpg'))->folder('Test folder/Images')->save();
    Upload::make(UploadedFile::fake()->image('avatar-3.jpg'))->folder('Test folder/Images/More')->save();
    Upload::make(UploadedFile::fake()->image('avatar-4.jpg'))->folder('Test folder/Images/More')->save();
    Upload::make(UploadedFile::fake()->image('avatar-5.jpg'))->folder('Test folder/Other')->save();

    $folder = MediaStorage::active()->resolveFolder('Test folder');
    $zipUrl = $folder->zip();

    $zip = new \ZipArchive();

    $zip->open($zipUrl);

    $stat = $zip->statIndex(0);
    expect($stat)->toBeArray();
    expect($stat['name'])->toEqual('Test folder/avatar-1.jpg');

    $stat = $zip->statIndex(1);
    expect($stat)->toBeArray();
    expect($stat['name'])->toEqual('Test folder/Images/avatar-2.jpg');

    $stat = $zip->statIndex(2);
    expect($stat)->toBeArray();
    expect($stat['name'])->toEqual('Test folder/Images/More/avatar-3.jpg');

    $stat = $zip->statIndex(3);
    expect($stat)->toBeArray();
    expect($stat['name'])->toEqual('Test folder/Images/More/avatar-4.jpg');

    $stat = $zip->statIndex(4);
    expect($stat)->toBeArray();
    expect($stat['name'])->toEqual('Test folder/Other/avatar-5.jpg');

    $zip->close();
});

// it('should set created folder privacy', function () {
//     $disk = 'local';
//     Storage::fake($disk);
//     $storage = MediaStorage::create(['name' => 'Products', 'disk' => $disk]);
//     /** @var Folder */
//     $folder = $storage->assertFolder('Images/Products');

//     $path = $folder->path();

//     expect(Storage::disk($disk)->getVisibility($path))->toEqual('private');
//     expect($folder->private)->toBeFalse();
//     $folder->setPrivate();
//     expect($folder->private)->toBeTrue();
//     expect(Storage::disk($disk)->getVisibility($path))->toEqual('public');
// });
