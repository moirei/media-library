<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('file', 'move');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
});

it('should move uploaded file', function () {
    $disk = 'local';
    // Storage::fake($disk);
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    /** @var File */
    $uploadedFile = Upload::make(UploadedFile::fake()->image('avatar.jpg'))->save();

    Storage::disk($disk)->assertExists($oldUri = $uploadedFile->uri());

    MediaStorage::active()->move($uploadedFile, 'images/products');
    expect($oldUri)->not->toEqual($uploadedFile->uri());
    Storage::disk($disk)->assertMissing($oldUri);

    $uploadedFile->forceDelete();
    Storage::disk($disk)->assertMissing($uploadedFile->uri());
});


it('should move created folder', function () {
    $disk = 'local';
    Storage::fake($disk);
    /** @var MediaStorage */
    $storage = MediaStorage::create(['name' => 'Products', 'disk' => $disk]);

    $folder = $storage->assertFolder('Images');
    $oldPath = $folder->path();

    expect(Storage::disk($storage->disk)->exists($oldPath))->toBeTrue();

    $storage->moveFolder($folder, 'Files');

    expect(Storage::disk($storage->disk)->exists($folder->path()))->toBeTrue();
    expect(Storage::disk($storage->disk)->exists($oldPath))->toBeFalse();
});

it('should move created folder and their files', function () {
    $disk = 'local';
    Storage::fake($disk);
    /** @var MediaStorage */
    $storage = MediaStorage::createAndUse(['name' => 'Products', 'disk' => $disk]);

    $folder = $storage->assertFolder('Images');

    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $file = Upload::make($uploadedFile)->folder($folder)->save();

    $oldPath = $folder->path();
    $fileUri = $file->uri();

    expect(Storage::disk($storage->disk)->exists($oldPath))->toBeTrue();
    Storage::disk($storage->disk)->assertExists($fileUri);

    $storage->moveFolder($folder, 'Files');
    $file->refresh();

    expect(Storage::disk($storage->disk)->exists($folder->path()))->toBeTrue();
    expect(Storage::disk($storage->disk)->exists($oldPath))->toBeFalse();
    Storage::disk($storage->disk)->assertMissing($fileUri);
    expect($file->uri())->not->toEqual($fileUri);
});
