<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'media-storage-files');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    $disk = 'local';
    Storage::fake($disk);
    $this->mediaStorage = MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk,
    ]);
});

it('should get file in storage via ID', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    /** @var MediaStorage */
    $mediaStorage = $this->mediaStorage;
    $upload = Upload::uploadFile($uploadedFile);

    $file = $mediaStorage->findFile($upload->id);
    expect($file)->not->toBeNull();
    expect($file->id)->toEqual($upload->id);
});

it('should get file in storage via FQFN', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    /** @var MediaStorage */
    $mediaStorage = $this->mediaStorage;
    $upload = Upload::uploadFile($uploadedFile);

    $file = $mediaStorage->findFile($upload->fqfn);
    expect($file)->not->toBeNull();
    expect($file->id)->toEqual($upload->id);
});

it('should get file in storage via options', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    /** @var MediaStorage */
    $mediaStorage = $this->mediaStorage;
    $upload = Upload::uploadFile($uploadedFile);

    $file = $mediaStorage->findFile([
        'id' => $upload->id,
        'private' => $upload->private,
    ]);
    expect($file)->not->toBeNull();
    expect($file->id)->toEqual($upload->id);
});

it('should NOT get file in other storage', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    Storage::fake('other');
    /** @var MediaStorage */
    $mediaStorage = MediaStorage::create([
        'name' => 'Other',
        'disk' => 'other',
    ]);
    $upload = Upload::uploadFile($uploadedFile);

    $file = $mediaStorage->findFile($upload->fqfn);
    expect($file)->toBeNull();
});


it('should get folder in storage via ID', function () {
    /** @var MediaStorage */
    $mediaStorage = $this->mediaStorage;
    $createdFolder = $mediaStorage->createFolder([
        'name' => 'Test folder'
    ]);

    $folder = $mediaStorage->findFolder($createdFolder->id);
    expect($folder)->not->toBeNull();
    expect($folder->id)->toEqual($createdFolder->id);
});

it('should get file in storage via path', function () {
    /** @var MediaStorage */
    $mediaStorage = $this->mediaStorage;
    $createdFolder = $mediaStorage->createFolder([
        'name' => 'Test folder',
        'location' => 'images'
    ]);

    $folder = $mediaStorage->findFolder('images/Test folder');
    expect($folder)->not->toBeNull();
    expect($folder->id)->toEqual($createdFolder->id);
});

it('should get folder in storage via options', function () {
    /** @var MediaStorage */
    $mediaStorage = $this->mediaStorage;
    $createdFolder = $mediaStorage->createFolder([
        'name' => 'Test folder',
    ]);

    $folder = $mediaStorage->findFolder([
        'id' => $createdFolder->id,
        'private' => $createdFolder->private,
    ]);
    expect($folder)->not->toBeNull();
    expect($folder->id)->toEqual($createdFolder->id);
});

it('should NOT get folder in other storage', function () {
    $createdFolder = $this->mediaStorage->createFolder([
        'name' => 'Test folder',
    ]);

    /** @var MediaStorage */
    $mediaStorage = MediaStorage::create([
        'name' => 'Other',
        'disk' => 'other',
    ]);

    $file = $mediaStorage->findFolder($createdFolder->id);
    expect($file)->toBeNull();
});
