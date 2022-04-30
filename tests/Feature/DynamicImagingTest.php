<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'dynamic-imaging');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);


    $disk = 'local';
    Storage::fake($disk);
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');
    MediaStorage::createAndUse([
        'name' => 'Avatars',
        'disk' => $disk
    ]);

    $this->file = Upload::uploadFile($uploadedFile);
});

it('should get image with resized width', function () {
    $url = URL::route('media.image', [
        'file' => $this->file->fqfn,
        'width' => $width = 350,
        // 'q' => 34,
        // 'func' => [
        //     'invert',
        //     'grey',
        //     'crop:23,45',
        // ],
    ], false);

    /** @var Response */
    $response = $this->get($url);

    // $response->assertDownload();

    $response->assertStatus(200);

    $manager = new ImageManager();
    /** @var Image */
    $image = $manager->make($response->content());
    expect($image->getWidth())->toEqual($width);
});

it('should get image with resized height', function () {
    $url = URL::route('media.image', [
        'file' => $this->file->fqfn,
        'height' => $height = 200,
    ], false);

    /** @var Response */
    $response = $this->get($url);

    $response->assertStatus(200);

    $manager = new ImageManager();
    /** @var Image */
    $image = $manager->make($response->content());
    expect($image->getHeight())->toEqual($height);
});

it('should get image with resized width & height', function () {
    $url = URL::route('media.image', [
        'file' => $this->file->fqfn,
        'w' => $width = 350,
        'h' => $height = 200,
    ], false);

    /** @var Response */
    $response = $this->get($url);

    $response->assertStatus(200);

    $manager = new ImageManager();
    /** @var Image */
    $image = $manager->make($response->content());

    expect($image->getWidth())->toEqual($width);
    expect($image->getHeight())->toEqual($height);
});

// it('should crop image with width and height', function () {
//     $url = URL::route('media.image', [
//         'file' => $this->file->fqfn,
//         'w' => $width = 350,
//         'h' => $height = 200,
//         'func' => 'crop',
//     ], false);

//     /** @var Response */
//     $response = $this->get($url);

//     $response->assertStatus(200);

//     $manager = new ImageManager();
//     /** @var Image */
//     $image = $manager->make($response->content());

//     expect($image->getWidth())->toEqual($width);
//     expect($image->getHeight())->toEqual($height);
// });
