<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'browse', 'browse-api-routes');

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

    $this->files = [];

    $files = [
        [
            'filename' => 'avatar-root.jpg',
            'private' => false,
            'location' => null,
        ],
        [
            'filename' => 'avatar-1.jpg',
            'private' => false,
            'location' => 'images',
        ],
        [
            'filename' => 'avatar-2.png',
            'private' => true,
            'location' => 'images',
        ],
        [
            'filename' => 'avatar-3.jpg',
            'private' => false,
            'location' => 'images/products',
        ],
    ];

    foreach ($files as $options) {
        $file = UploadedFile::fake()->image($options['filename']);
        $this->files[] = Upload::make($file)
            ->private($options['private'])
            ->location($options['location'] ?? '')
            ->save();
    }

    $this->user = new class(['name' => 'John']) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $this->be($this->user);
});

it('should browse ALL root files', function () {
    $response = $this->post(route('media.browse'));
    $response->assertStatus(200);
    $results = $response->json();
    expect(count($results))->toEqual(2);
});

it('should browse files in root location', function () {
    $response = $this->post(route('media.browse'), [
        'filesOnly' => true,
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(1);
    expect($results[0])->toHaveKeys(['id', 'fqfn', 'name']);
    expect($results[0]['id'])->toEqual($this->files[0]->id);
    expect($results[0]['fqfn'])->toEqual($this->files[0]->fqfn);
    expect($results[0]['name'])->toEqual($this->files[0]->name);
});

it('should browse files in location', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images',
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(3);
});

it('should browse public files in location', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images',
        'private' => false
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(2);
});

it('should browse image files in location', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images',
        'type' => 'image'
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(2);
});

it('should browse JPEG files in location', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images',
        'mime' => 'jpeg'
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(1);
});

it('should browse files in location [subfolder]', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images/products',
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(1);
});

it('should browse public files in location [subfolder]', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images/products',
        'private' => false
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(1);
});

it('should browse private files in location [subfolder]', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images/products',
        'private' => true,
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(0);
});

it('should browse image files in location [subfolder]', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images/products',
        'type' => 'image'
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(1);
});

it('should browse JPEG files in location [subfolder]', function () {
    $response = $this->post(route('media.browse'), [
        'location' => 'images/products',
        'mime' => 'jpeg'
    ]);
    $response->assertStatus(200);
    $results = Arr::get($response->json(), 'data');
    expect(count($results))->toEqual(1);
});
