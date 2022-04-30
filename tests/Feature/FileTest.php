<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'file');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $disk = 'local';
    Storage::fake($disk);
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk,
    ]);

    $this->file = Upload::uploadFile($uploadedFile);
});

it('should generate signed url', function () {
    $this->file->setPrivate(true);
    $url = $this->file->url(0);
    expect($url)->toContain('expires');
    expect($url)->toContain('signature');
});

// it('should set created file privacy', function () {
//     $path = $this->file->path();

//     expect(Storage::disk($this->file->disk())->getVisibility($path))->toEqual('private');
//     expect($this->file->private)->toBeFalse();
//     $this->file->setPrivate();
//     expect($this->file->private)->toBeTrue();
//     expect(Storage::disk($this->file->disk())->getVisibility($path))->toEqual('public');
// });
