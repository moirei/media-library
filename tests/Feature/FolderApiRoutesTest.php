<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'folder-api-routes');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => 'local',
    ]);

    $this->file = Upload::uploadFile($uploadedFile);
    $this->user = new class(['name' => 'John']) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $this->be($this->user);
});

it('should create folder', function () {
    Storage::fake('local');
    $response = $this->post(route('media.folder.create'), [
        'name' => 'Test folder',
        'location' => 'test/location',
        'description' => 'Test folder description',
    ]);

    $response->assertStatus(200);
    $fileJson = $response->json();

    expect($fileJson)->toHaveKeys(['id', 'name', 'location', 'description']);
    expect(Arr::get($fileJson, 'name'))->toEqual('Test folder');
    expect(Arr::get($fileJson, 'location'))->toEqual('test/location');
    expect(Arr::get($fileJson, 'description'))->toEqual('Test folder description');

    $folder = new Folder($fileJson);
    Storage::disk('local')->assertExists($folder->path());
});

it('should get folder data', function () {
    $folder = MediaStorage::active()->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
        'private' => true,
    ]);

    $url = route('media.folder-data.protected', ['folder' => $folder->id]);
    $response = $this->get($url);
    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toBeArray();
    expect($data['id'])->toEqual($folder->id);
    expect($data['name'])->toEqual($folder->name);
});

it('should update folder', function () {
    Storage::fake('local');
    $storage = MediaStorage::active();
    $oldFolder = $storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
        'private' => true,
    ]);

    $url = route('media.folder.update', ['folder' => $oldFolder->id]);
    $response = $this->post($url, [
        'name' => 'Updated name',
        'description' => 'Updated description',
    ]);

    $response->assertStatus(200);
    $updatedFolder = $response->json();

    // $updatedFolder = Folder::find($oldFolder->id);

    expect($updatedFolder)->toHaveKeys(['id', 'name', 'description']);
    expect(Arr::get($updatedFolder, 'id'))->toEqual($oldFolder->id);
    expect(Arr::get($updatedFolder, 'name'))->toEqual('Updated name');
    expect(Arr::get($updatedFolder, 'description'))->toEqual('Updated description');
});

it('should move folder', function () {
    Storage::fake('local');
    $storage = MediaStorage::active();
    $oldFolder = $storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
    ]);

    $url = route('media.folder.move', ['folder' => $oldFolder->id]);
    $response = $this->post($url, [
        'location' => 'new/location',
    ]);
    $movedFolder = Folder::find($oldFolder->id);
    $response->assertStatus(200);

    expect($movedFolder->location)->toContain('new/location');
    expect($movedFolder->location)->not->toEqual($oldFolder->location);
    expect($movedFolder->path())->not->toEqual($oldFolder->path());
    Storage::disk('local')->assertExists($movedFolder->path());
    Storage::disk('local')->assertMissing($oldFolder->path());
});

it('should move folder using id', function () {
    Storage::fake('local');
    $storage = MediaStorage::active();
    $oldFolder = $storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
    ]);
    $newFolder = $storage->createFolder([
        'name' => 'New Folder',
        'location' => 'new/location',
    ]);

    $url = route('media.folder.move', ['folder' => $oldFolder->id]);
    $response = $this->post($url, [
        'location' => $newFolder->id,
    ]);
    $movedFolder = Folder::find($oldFolder->id);
    $response->assertStatus(200);

    expect($movedFolder->location)->toContain('new/location');
    expect($movedFolder->location)->not->toEqual($oldFolder->location);
    expect($movedFolder->path())->not->toEqual($oldFolder->path());
    Storage::disk('local')->assertExists($movedFolder->path());
    Storage::disk('local')->assertMissing($oldFolder->path());
});

it('should delete folder', function () {
    Storage::fake('local');
    $storage = MediaStorage::active();
    $oldFolder = $storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
    ]);

    $url = route('media.folder.delete', ['folder' => $oldFolder->id]);
    $response = $this->delete($url);
    $deletedFolder = Folder::find($oldFolder->id);

    $response->assertStatus(200);
    expect($deletedFolder)->toBeNull();
    Storage::disk('local')->assertExists($oldFolder->path());
});

it('should force delete folder', function () {
    Storage::fake('local');
    $storage = MediaStorage::active();
    $oldFolder = $storage->createFolder([
        'name' => 'Images',
        'location' => 'accessories',
    ]);

    $url = route('media.folder.delete', ['folder' => $oldFolder->id, 'force' => true]);
    $response = $this->delete($url);

    $response->assertStatus(200);
    Storage::disk('local')->assertMissing($oldFolder->path());
});
