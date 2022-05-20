<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'folder-downloadable-link-api-routes');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    $mediaStorage = MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => 'local',
    ]);

    $this->folder = $mediaStorage->createFolder([
        'name' => 'Test folder',
    ]);

    $this->user = new class(['name' => 'John']) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $this->be($this->user);
});

it('should get downloadable link', function () {
    $url = route('media.folder.downloadable-link', ['folder' => $this->folder->id]);
    $link = Arr::get($this->get($url)->json(), 'url');
    expect($link)->toContain($this->folder->id);
    expect($link)->toContain('download');
});

it('should get downloadable via POST', function () {
    $url = route('media.folder.downloadable-link', ['folder' => $this->folder->id]);
    $link = Arr::get($this->post($url)->json(), 'url');
    expect($link)->toContain($this->folder->id);
    expect($link)->toContain('download');
});

it('should get downloadable link for private folder', function () {
    $url = route('media.folder.downloadable-link', ['folder' => $this->folder->id]);
    $this->folder->setPrivate(true);
    $response = $this->get($url);
    $link = Arr::get($response->json(), 'url');
    expect($link)->toContain($this->folder->id);
    expect($link)->toContain('download');
    expect($link)->toContain('expires');
    expect($link)->toContain('signature');
});

it('should download via downloadable link', function () {
    $url = route('media.folder.downloadable-link', ['folder' => $this->folder->id]);
    $link = Arr::get($this->get($url)->json(), 'url');

    Upload::make(UploadedFile::fake()->image('avatar-1.jpg'))->folder($this->folder)->save();

    $response = $this->get($link);
    $response->assertStatus(200);

    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
    // $response->assertHeader('Content-Type', 'application/zip');
    // $response->assertHeader('Content-Type', 'application/octet-stream');
});

it('should download private via downloadable link', function () {
    $url = route('media.folder.downloadable-link', ['folder' => $this->folder->id]);
    $this->folder->setPrivate(true);
    $link = Arr::get($this->get($url)->json(), 'url');

    Upload::make(UploadedFile::fake()->image('avatar-1.jpg'))->folder($this->folder)->save();

    expect($link)->toContain('expires');
    expect($link)->toContain('signature');

    $response = $this->get($link);
    $response->assertStatus(200);

    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});
