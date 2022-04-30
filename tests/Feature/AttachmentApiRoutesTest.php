<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\Attachment;
use MOIREI\MediaLibrary\Upload;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('api-routes', 'attachment-api-routes');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);

    $this->disk = config('media-library.uploads.attachments.disk', 'local');
    Storage::fake($this->disk);
    $this->user = new class(['name' => 'John']) extends \Illuminate\Foundation\Auth\User
    {
        protected $fillable = ['*'];
    };
    $this->be($this->user);
});

it('should upload an attachment', function () {
    $uploadedFile = UploadedFile::fake()->image('attachment.jpg');

    $response = $this->post(route('media.attachment.store'), [
        'file' => $uploadedFile
    ]);

    $response->assertStatus(200);
    $attachmentJson = $response->json();
    $attachment = new Attachment($attachmentJson);

    expect($attachmentJson)->toHaveKeys(['id', 'alt', 'url']);
    expect(Arr::get($attachmentJson, 'id'))->toBeString();
    expect(Arr::get($attachmentJson, 'alt'))->toBeString();
    expect(Arr::get($attachmentJson, 'url'))->toBeString();

    Storage::disk('local')->assertExists($attachment->uri());
});

it('should delete an attachment', function () {
    $uploadedFile = UploadedFile::fake()->image('attachment.jpg');
    $attachment = Upload::uploadAttachment($uploadedFile);
    $path = $attachment->uri();

    $url = route('media.attachment.destroy', [
        'attachment' => $attachment->id,
    ]);
    $response = $this->delete($url, [
        'file' => $uploadedFile
    ]);

    $response->assertStatus(200);
    $attachmentJson = $response->json();
    $attachment = new Attachment($attachmentJson);

    expect($attachmentJson)->toHaveKeys(['id', 'alt', 'url']);
    expect(Arr::get($attachmentJson, 'id'))->toEqual($attachment->id);
    expect(Arr::get($attachmentJson, 'alt'))->toEqual($attachment->alt);
    expect(Arr::get($attachmentJson, 'url'))->toEqual($attachment->url);

    Storage::disk('local')->assertMissing($path);
});
