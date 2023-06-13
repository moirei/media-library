<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\Exceptions\MediaLocationUpdateException;
use MOIREI\MediaLibrary\Exceptions\StorageDiskUpdateException;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'storage');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
});

it('should create storage', function () {
    $storage = MediaStorage::create([
        'name' => 'Products',
        'location' => 'products',
        'description' => 'products storage',
        'disk' => 's3',
        'private' => false,
        'capacity' => 20000,
    ]);

    expect($storage)->toBeInstanceOf(MediaStorage::class);
    expect(Api::isUuid($storage->id))->toBeTrue();
    expect(MediaStorage::query()->count())->toEqual(1);
});

it('should create storage with defaults', function () {
    $storage = MediaStorage::create([
        'name' => 'Products',
    ]);

    expect($storage->location)->toEqual('products');
    expect($storage->disk)->toEqual(config('media-library.storage.disk'));
});

it('expects storage to resolve paths', function () {
    $storage = MediaStorage::create([
        'name' => 'Products',
        'location' => 'products',
    ]);

    expect($storage->path())->toEqual(Api::joinPaths('media', 'files', 'products'));
    expect($storage->path('Images'))->toEqual(Api::joinPaths('media', 'files', 'products', 'Images'));
    expect($storage->path(Api::joinPaths('Images', 'Products')))->toEqual(Api::joinPaths('media', 'files', 'products', 'Images', 'Products'));
    expect($storage->path('Images', 'Products'))->toEqual(Api::joinPaths('media', 'files', 'products', 'Images', 'Products'));
});

it('expects UNUSED storage to be empty', function () {
    $storage = MediaStorage::create([
        'name' => 'Products',
        'location' => 'products',
    ]);

    expect($storage->isEmpty())->toBeTrue();
    expect($storage->exists())->toBeTrue();
});

it('expects USED storage to NOT be empty', function () {
    $storage = MediaStorage::createAndUse([
        'name' => 'Products',
        'location' => 'products',
    ]);

    $file = UploadedFile::fake()->image('avatar.jpg');
    Upload::uploadFile($file);

    expect($storage->isEmpty())->toBeFalse();
});

it('should update storage location and disk', function () {
    /** @var MediaStorage */
    $storage = MediaStorage::create([
        'name' => 'Products',
        'location' => 'products',
    ]);

    $oldLocation = $storage->location;
    $oldDisk = $storage->disk;

    $storage->update([
        'location' => 'products-2',
        'disk' => 's4',
    ]);

    expect($storage->location)->toEqual('products-2');
    expect($storage->location)->not->toEqual($oldLocation);
    expect($storage->disk)->not->toEqual($oldDisk);
});

it('should not update storage disk when in use', function () {
    $storage = MediaStorage::createAndUse([
        'name' => 'Products',
        'location' => 'products',
    ]);

    $file = UploadedFile::fake()->image('avatar.jpg');
    Upload::uploadFile($file);

    $storage->update([
        'disk' => 's4',
    ]);
})->throws(StorageDiskUpdateException::class);

it('should not update storage location when in use', function () {
    $storage = MediaStorage::createAndUse([
        'name' => 'Products',
        'location' => 'products',
    ]);

    $file = UploadedFile::fake()->image('avatar.jpg');
    Upload::uploadFile($file);

    $storage->update([
        'location' => 'products-2',
    ]);
})->throws(MediaLocationUpdateException::class);

it('should delete all storage content', function () {
    $disk = 'local';
    Storage::fake($disk);
    /** @var MediaStorage */
    $storage = MediaStorage::createAndUse(['name' => 'Products', 'disk' => $disk]);

    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    Upload::make($uploadedFile)->location('Images')->save();

    expect($storage->files()->count())->toEqual(1);
    expect($storage->folders()->count())->toEqual(1);

    $storage->deleteAllContent();

    expect($storage->files()->count())->toEqual(0);
    expect($storage->folders()->count())->toEqual(0);
    Storage::disk($disk)->assertExists($storage->path());
});

it('should delete all storage content including physical storage', function () {
    $disk = 'local';
    Storage::fake($disk);
    /** @var MediaStorage */
    $storage = MediaStorage::createAndUse(['name' => 'Products', 'disk' => $disk]);

    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    Upload::make($uploadedFile)->location('Images')->save();

    expect($storage->files()->count())->toEqual(1);
    expect($storage->folders()->count())->toEqual(1);

    $storage->deleteAllContent(true);

    expect($storage->files()->count())->toEqual(0);
    expect($storage->folders()->count())->toEqual(0);
    Storage::disk($disk)->assertMissing($storage->path());
});

// it('should set created storage privacy', function () {
//     $disk = 'local';
//     Storage::fake($disk);
//     /** @var MediaStorage */
//     $storage = MediaStorage::createAndUse([
//         'name' => 'Products',
//         'disk' => $disk,
//         'private' => false
//     ]);

//     $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
//     $file = Upload::make($uploadedFile)->save();

//     $filePath = $file->uri();

//     Storage::disk($disk)->assertExists($filePath);
//     expect(Storage::disk($disk)->getVisibility($filePath))->toEqual('private');
//     expect(Storage::disk($disk)->getVisibility($storage->path()))->toEqual('private');

//     expect($storage->private)->toBeFalse();
//     $storage->setPrivate();
//     expect($storage->private)->toBeTrue();
//     expect(Storage::disk($disk)->getVisibility($filePath))->toEqual('public');
//     expect(Storage::disk($disk)->getVisibility($storage->path()))->toEqual('public');
// });

// it('should create storage with model owner', function () {
//     $storage = MediaStorage::create([
//         'name' => 'Products',
//     ]);

//     expect($storage->location)->toEqual('products');
//     expect($storage->disk)->toEqual(config('media-library.storage.disk'));
// });
