<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\MediaStorage;

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

it('should create multiple folder from assert', function () {
    /** @var MediaStorage */
    $storage = MediaStorage::create(['name' => 'Products']);
    $folder = $storage->assertFolder('Images/Products');

    expect($folder)->toBeInstanceOf(Folder::class);
    expect(Folder::query()->count())->toEqual(2);
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
