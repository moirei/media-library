<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\File;
use MOIREI\MediaLibrary\Traits\InteractsWithMedia;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'attachment');

// use file as attachable model
class TestModel extends File
{
    use InteractsWithMedia;

    protected $guarded = [];
    protected $richTextFields = ['description'];
    protected $table = 'files';
}

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    $this->disk = 'public';
    Storage::fake($this->disk);
    $file = UploadedFile::fake()->image('attachment.jpg');

    $attributes = Upload::uploadFile($file)->getAttributes();
    $attributes['id']++; // force unique
    $attributes['fqfn'] = $attributes['fqfn'] . $attributes['id']; // force unique
    $this->model = new TestModel($attributes);

    $this->uploadedAttachment = Upload::uploadAttachment($file);
});

it('should attach model to attachment', function () {
    $this->uploadedAttachment->attach($this->model);

    expect($this->uploadedAttachment->getAttribute('attachable_type'))->toEqual($this->model->getMorphClass());
    expect($this->uploadedAttachment->getAttribute('attachable_id'))->toEqual($this->model->getKey());
});

it('should attach model to attachment via model update', function () {
    $this->model->description = "
    <img src=\"{$this->uploadedAttachment->url}\" alt=\"{$this->uploadedAttachment->alt}\" >
    ";
    $this->model->save();
    $this->uploadedAttachment->refresh();

    expect($this->uploadedAttachment->getAttribute('attachable_type'))->toEqual($this->model->getMorphClass());
    expect($this->uploadedAttachment->getAttribute('attachable_id'))->toEqual($this->model->getKey());
});
