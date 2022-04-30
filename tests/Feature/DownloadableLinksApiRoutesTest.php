<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'downloadable-link-api-routes');

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

it('should get downloadable link', function () {
    $url = route('media.downloadable-link', ['file' => $this->file->id]);
    $link = Arr::get($this->get($url)->json(), 'url');
    expect($link)->toContain($this->file->id);
    expect($link)->toContain('download');
});

it('should get downloadable via POST', function () {
    $url = route('media.downloadable-link', ['file' => $this->file->id]);
    $link = Arr::get($this->post($url)->json(), 'url');
    expect($link)->toContain($this->file->id);
    expect($link)->toContain('download');
});

it('should get downloadable link for private file', function () {
    $url = route('media.downloadable-link', ['file' => $this->file->id]);
    $this->file->setPrivate(true);
    $response = $this->get($url);
    $link = Arr::get($response->json(), 'url');
    expect($link)->toContain($this->file->id);
    expect($link)->toContain('download');
    expect($link)->toContain('expires');
    expect($link)->toContain('signature');
});

it('should download via downloadable link', function () {
    $url = route('media.downloadable-link', ['file' => $this->file->id]);
    $link = Arr::get($this->get($url)->json(), 'url');

    $response = $this->get($link);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

it('should download private via downloadable link', function () {
    $url = route('media.downloadable-link', ['file' => $this->file->id]);
    $this->file->setPrivate(true);
    $link = Arr::get($this->get($url)->json(), 'url');

    expect($link)->toContain('expires');
    expect($link)->toContain('signature');

    $response = $this->get($link);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});
