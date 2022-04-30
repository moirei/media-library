<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'browse');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
});

it('should browse root files', function () {
    $disk = 'local';
    $file = UploadedFile::fake()->image('avatar.jpg');
    $storage = MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $uploadedFile = Upload::uploadFile($file);
    $results = $storage->browse();

    expect($results)->toBeCollection();
    expect($results->count())->toEqual(1);
    expect($results->first())->toBeInstanceOf(File::class);
    expect($results->first()->is($uploadedFile))->toBeTrue();
});

it('should browse all files', function () {
    $disk = 'local';
    $storage = MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $files = [
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

    $uploads = [];

    foreach ($files as $options) {
        $file = UploadedFile::fake()->image($options['filename']);
        $uploads[] = Upload::make($file)
            ->private($options['private'])
            ->location($options['location'])
            ->save();
    }

    expect($storage->browse('images')->count())->toEqual(3);
    expect($storage->browse('images', ['private' => false])->count())->toEqual(2);
    expect($storage->browse('images', ['type' => 'image'])->count())->toEqual(2);
    expect($storage->browse('images', ['mime' => 'jpeg'])->count())->toEqual(1);

    expect($storage->browse('images/products')->count())->toEqual(1);
    expect($storage->browse('images/products', ['private' => false])->count())->toEqual(1);
    expect($storage->browse('images/products', ['private' => true])->count())->toEqual(0);
    expect($storage->browse('images/products', ['type' => ['image']])->count())->toEqual(1);
    expect($storage->browse('images/products', ['mime' => ['jpeg']])->count())->toEqual(1);
});
