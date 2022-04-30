<?php

use Illuminate\Database\Eloquent\Model;
use MOIREI\MediaLibrary\Api;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Traits\InteractsWithMedia;

uses()->group('utils', 'api');

beforeEach(function () {
    app()->register(MediaLibraryServiceProvider::class);
});

it('should get file type', function () {
    expect(Api::getFileType('image/png'))->toEqual('image');
    expect(Api::getFileType('image/jpeg'))->toEqual('image');
    expect(Api::getFileType('image/svg+xml'))->toEqual('image');
    expect(Api::getFileType('audio/mp3'))->toEqual('audio');
    expect(Api::getFileType('video/mp4'))->toEqual('video');
    expect(Api::getFileType('application/pdf'))->toEqual('docs');
    expect(Api::getFileType('application/unknown'))->toEqual('other');
    expect(Api::getFileType('a/b'))->toEqual('b');
    expect(Api::getFileType('a'))->toEqual('a');
});


it('should have InteractsWithMedia trait', function () {
    $model = new class() extends Model
    {
        use InteractsWithMedia;
    };

    expect(Api::interactsWithMedia($model))->toBeTrue();
});


it('should not have InteractsWithMedia trait', function () {
    $model = new class() extends Model
    {
        //
    };

    expect(Api::interactsWithMedia($model))->toBeFalse();
});
