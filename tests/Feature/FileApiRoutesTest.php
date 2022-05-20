<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'file-api-routes');

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

it('should get file data', function () {
    $url = route('media.file-data.protected', ['file' => $this->file->id]);
    $response = $this->get($url);
    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toBeArray();
    expect($data['id'])->toEqual($this->file->id);
    expect($data['name'])->toEqual($this->file->name);
});

it('should upload file', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->post(route('media.upload'), [
        'file' => $uploadedFile
    ]);

    $response->assertStatus(200);
    $fileJson = $response->json();

    expect($fileJson)->toHaveKeys(['id', 'name', 'filename', 'type', 'mime', 'mimetype']);
    expect(Arr::get($fileJson, 'name'))->toEqual('avatar');

    $file = new File($fileJson);
    Storage::disk('local')->assertExists($file->uri());
});

it('should update file', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $oldFile = Upload::uploadFile($uploadedFile);

    $url = route('media.update', ['file' => $oldFile->id]);
    $response = $this->post($url, [
        'name' => 'Updated name',
        'description' => 'Updated description',
    ]);

    $response->assertStatus(200);
    $fileJson = $response->json();

    $updatedFile = File::find($oldFile->id);

    expect($fileJson)->toHaveKeys(['id', 'name', 'description']);
    expect(Arr::get($fileJson, 'name'))->toEqual('Updated name');
    expect(Arr::get($fileJson, 'name'))->toEqual($updatedFile->name);
    expect(Arr::get($fileJson, 'description'))->toEqual('Updated description');
    expect(Arr::get($fileJson, 'description'))->toEqual($updatedFile->description);
});

it('should move file', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $oldFile = Upload::uploadFile($uploadedFile);

    $url = route('media.move', ['file' => $oldFile->id]);
    $response = $this->post($url, [
        'location' => 'new/location',
    ]);
    $movedFile = File::find($oldFile->id);

    $response->assertStatus(200);

    expect($movedFile->location)->toContain('new/location');
    expect($movedFile->location)->not->toEqual($oldFile->location);
    expect($movedFile->uri())->not->toEqual($oldFile->uri());
    Storage::disk('local')->assertExists($movedFile->uri());
    Storage::disk('local')->assertMissing($oldFile->uri());
});

it('should delete file', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $oldFile = Upload::uploadFile($uploadedFile);

    $url = route('media.delete', ['file' => $oldFile->id]);
    $response = $this->delete($url);
    $deletedFile = File::find($oldFile->id);

    $response->assertStatus(200);
    expect($deletedFile)->toBeNull();
    Storage::disk('local')->assertExists($oldFile->uri());
});

it('should force delete file', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    $oldFile = Upload::uploadFile($uploadedFile);

    $url = route('media.delete', ['file' => $oldFile->id, 'force' => true]);
    $response = $this->delete($url);

    $response->assertStatus(200);
    Storage::disk('local')->assertMissing($oldFile->uri());
});
