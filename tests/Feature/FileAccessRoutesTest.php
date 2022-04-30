<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'file-access-routes');

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
});

it('should get public file', function () {
    $url = route('media.file', ['file' => $this->file->fqfn]);
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
    $response->assertHeader('Content-Disposition', 'inline');
});

it('should public file via id', function () {
    $url = route('media.file', ['file' => $this->file->id]);
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
    $response->assertHeader('Content-Disposition', 'inline');
});

it('should fail to get private file', function () {
    $url = route('media.file', ['file' => $this->file->fqfn]);
    $this->file->setPrivate(true);
    $response = $this->get($url);
    $response->assertStatus(401);
});

it('should download public file', function () {
    $url = route('media.download', ['file' => $this->file->fqfn]);
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

it('should fail to download private file', function () {
    $url = route('media.download', ['file' => $this->file->fqfn]);
    $this->file->setPrivate(true);
    $response = $this->get($url);
    $response->assertStatus(401);
});

it('should stream public file', function () {
    $url = route('media.stream', ['file' => $this->file->fqfn]);
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
});

it('should fail to stream private file', function () {
    $url = route('media.stream', ['file' => $this->file->fqfn]);
    $this->file->setPrivate(true);
    $response = $this->get($url);
    $response->assertStatus(401);
});

it('should NOT access private/protected file via protected url', function () {
    $this->file->setPrivate(true);
    $url = route('media.file.protected', ['file' => $this->file->fqfn]);
    $response = $this->get($url);
    expect($response->status())->not->toEqual(200);
});

it('should access private/protected file', function () {
    $user = new class(array('name' => 'John')) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $this->file->setPrivate(true);
    $url = route('media.file.protected', ['file' => $this->file->fqfn]);
    $response = $this->actingAs($user)->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
});

it('should download private/protected file', function () {
    $user = new class(array('name' => 'John')) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $url = route('media.download.protected', ['file' => $this->file->fqfn]);
    $response = $this->actingAs($user)->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

it('should stream private/protected file', function () {
    $user = new class(['name' => 'John']) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $this->file->setPrivate(true);
    $url = route('media.stream.protected', ['file' => $this->file->fqfn]);
    $response = $this->actingAs($user)->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
});

it('should fail to access file via signed url', function () {
    $this->file->setPrivate(true);
    $url = route('media.file.signed', ['file' => $this->file->fqfn]);
    $response = $this->get($url);
    $response->assertStatus(403);
});

it('should fail to download file via signed url', function () {
    $this->file->setPrivate(true);
    $url = route('media.download.signed', ['file' => $this->file->fqfn]);
    $response = $this->get($url);
    $response->assertStatus(403);
});

it('should fail to stream file via signed url', function () {
    $this->file->setPrivate(true);
    $url = route('media.stream.signed', ['file' => $this->file->fqfn]);
    $response = $this->get($url);
    $response->assertStatus(403);
});

it('should access signed file', function () {
    $this->file->setPrivate(true);
    $url = $this->file->url();
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
    $response->assertHeader('Content-Disposition', 'inline');
});

it('should download signed file', function () {
    $this->file->setPrivate(true);
    $url = URL::temporarySignedRoute(
        'media.download.signed',
        now()->addMinutes(5),
        ['file' => $this->file->fqfn]
    );
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
    expect($response->headers->get('Content-Disposition'))->toContain('attachment');
});

it('should stream signed file', function () {
    $this->file->setPrivate(true);
    $url = URL::temporarySignedRoute(
        'media.stream.signed',
        now()->addMinutes(5),
        ['file' => $this->file->fqfn]
    );
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', $this->file->mimetype);
});

// should NOT be able to access private/protected file after TTL
// should NOT be able to stream private/protected file after TTL
// should NOT be able to download private/protected file after TTL

// should fail to access non image file