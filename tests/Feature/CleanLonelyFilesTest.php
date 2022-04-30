<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('commands', 'clean-lonely-files');

beforeEach(function () {
  include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
  (new \CreateMediaTables)->up();
  app()->register(MediaLibraryServiceProvider::class);

  $disk = 'local';
  Storage::fake($disk);
  $this->storage = MediaStorage::createAndUse([
    'name' => 'Avatars',
    'disk' => $disk,
  ]);
});

it('should execute media:clean:lonely-files command', function () {
  $this->artisan('media:clean:lonely-files')->assertExitCode(0);
});

it('should clean lonely files', function () {
  $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
  $file = Upload::uploadFile($uploadedFile);
  $path = $file->uri();

  Storage::assertExists($path);
  $this->artisan('media:clean:lonely-files')->assertExitCode(0);
  Storage::assertMissing($path);
});
