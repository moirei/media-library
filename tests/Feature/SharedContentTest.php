<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Models\SharedContent;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'shared-content');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    Storage::fake('local');
    $this->storage = MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => 'local',
    ]);
});

it('should create shared file content', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $file = Upload::uploadFile($uploadedFile);

    $shareable = SharedContent::make($file)
        ->downloads(2)
        ->public()
        ->save();

    expect($shareable->url())->toBeString();
});

it('should create shared file content directly from model', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $file = Upload::uploadFile($uploadedFile);

    $shareable = $file->share()->public()->save();

    expect($shareable)->toBeInstanceOf(SharedContent::class);
    expect($shareable->url())->toBeString();
});

it('should create shared folder content', function () {
    $folder = $this->storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
        'private' => true,
    ]);

    $shareable = SharedContent::make($folder)
        ->access('access-code')
        ->save();

    expect($shareable->url())->toBeString();
});

it('should create shared folder content directly from model', function () {
    /** @var Folder */
    $folder = $this->storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
        'private' => true,
    ]);

    $shareable = $folder->share()->public()->save();

    expect($shareable)->toBeInstanceOf(SharedContent::class);
    expect($shareable->url())->toBeString();
});

it('should limit access to shared content', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $file = Upload::uploadFile($uploadedFile);

    $shareable = $file->share()->access(SharedContent::ACCESS_TYPE_SECRET, [
        'code-1', 'code-2'
    ])
        ->canUpload()->save();

    expect($shareable->url())->toBeString();
    expect($shareable->access_type)->toEqual(SharedContent::ACCESS_TYPE_SECRET);
    expect($shareable->access_keys)->toBeArray();
    // expect(in_array(SharedContent::hashKey('code-1'), $shareable->access_keys))->toBeTrue();
    // expect(in_array(SharedContent::hashKey('code-2'), $shareable->access_keys))->toBeTrue();
});
