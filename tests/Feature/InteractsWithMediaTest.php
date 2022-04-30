<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Traits\InteractsWithMedia;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'interaction');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    $attributes = [
        'id' => 1,
    ];
    $this->model = new class($attributes) extends Model
    {
        use InteractsWithMedia;

        protected $guarded = [];
        public function save(array $options = [])
        {
            //
        }
    };
});


it('should upload and associate file', function () {
    $uploadedFile = UploadedFile::fake()->image('avatar.jpg');

    $file = Upload::uploadFile($uploadedFile);

    $this->model->media = $file;

    expect($file)->toBeInstanceOf(File::class);
});
