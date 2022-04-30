<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'shareable-link-api-routes');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
    $this->user = new class(['name' => 'John']) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $this->be($this->user);
});

it('should share a file', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    Storage::fake('local');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => 'local',
    ]);

    $file = Upload::uploadFile($uploadedFile);

    $url = route('media.file.share', ['file' => $file->id]);
    $response = $this->post($url, [
        'name' => 'Test shared content',
        'access_keys' => ['code-1']
    ]);
    $response->assertStatus(200);
    $sharedContentJson = $response->json();
    expect($sharedContentJson)->toHaveKeys(['id', 'url']);
    expect(in_array($sharedContentJson['id'], $file->shares->map->id->toArray()))->toBeTrue();
});

it('should share a folder', function () {
    Storage::fake('local');
    $storage = MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => 'local',
    ]);
    $folder = $storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
        'private' => true,
    ]);

    $url = route('media.folder.share', ['folder' => $folder->id]);
    $response = $this->post($url, [
        'name' => 'Test shared content',
        'access_keys' => ['code-1']
    ]);
    $response->assertStatus(200);
    $sharedContentJson = $response->json();
    expect($sharedContentJson)->toHaveKeys(['id', 'url']);
    expect(in_array($sharedContentJson['id'], $folder->shares->map->id->toArray()))->toBeTrue();
});
