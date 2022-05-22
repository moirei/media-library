<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'browse', 'browse-pagination');

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
    $this->totalFiles = 50;

    for ($index = 0; $index < $this->totalFiles; $index++) {
        $file = UploadedFile::fake()->image('image-' . $index . '.jpg');
        $this->files[] = Upload::uploadFile($file);
    }

    $this->user = new class(['name' => 'John']) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $this->be($this->user);
});

it('should paginate files [page 1]', function () {
    $response = $this->post(route('media.browse'), [
        'paginate' => [
            'page' => 1,
            'perPage' => 20
        ],
    ]);
    $response->assertStatus(200);

    $data = $response->json();

    expect($data)->toHaveKeys(['data', 'paginate']);
    expect(count($data['data']))->toEqual(20);
    expect($data['paginate'])->toHaveKeys(['total', 'pages', 'currentPage', 'perPage', 'prev', 'next']);
    expect($data['paginate']['total'])->toEqual($this->totalFiles);
    expect($data['paginate']['pages'])->toEqual(3);
    expect($data['paginate']['perPage'])->toEqual(20);
    expect($data['paginate']['currentPage'])->toEqual(1);
    expect($data['paginate']['prev'])->toBeNull();
    expect($data['paginate']['next'])->toEqual(2);
});

it('should paginate files [page 2]', function () {
    $response = $this->post(route('media.browse'), [
        'paginate' => [
            'page' => 2,
            'perPage' => 20
        ],
    ]);
    $response->assertStatus(200);

    $data = $response->json();

    expect($data)->toHaveKeys(['data', 'paginate']);
    expect(count($data['data']))->toEqual(20);
    expect($data['paginate']['total'])->toEqual($this->totalFiles);
    expect($data['paginate']['perPage'])->toEqual(20);
    expect($data['paginate']['currentPage'])->toEqual(2);
    expect($data['paginate']['prev'])->toEqual(1);
    expect($data['paginate']['next'])->toEqual(3);
});

it('should paginate files [page 3]', function () {
    $response = $this->post(route('media.browse'), [
        'paginate' => [
            'page' => 3,
            'perPage' => 20
        ],
    ]);
    $response->assertStatus(200);

    $data = $response->json();

    expect($data)->toHaveKeys(['data', 'paginate']);
    expect(count($data['data']))->toEqual(10);
    expect($data['paginate']['total'])->toEqual($this->totalFiles);
    expect($data['paginate']['perPage'])->toEqual(20);
    expect($data['paginate']['currentPage'])->toEqual(3);
    expect($data['paginate']['prev'])->toEqual(2);
    expect($data['paginate']['next'])->toBeNull();
});
