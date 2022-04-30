<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MOIREI\MediaLibrary\Exceptions\StorageRequiredException;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\Models\MediaStorage;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('storage', 'global-storage');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
});

it('should get created storage', function () {
    /** @var MediaStorage */
    $a = MediaStorage::create(['name' => 'Products']);
    $b = MediaStorage::get($a->id);

    expect($b)->toBeInstanceOf(MediaStorage::class);
    expect($b->name)->toEqual($a->name);
    expect($b->name)->toEqual('Products');
});

it('should get config storage', function () {
    $storage = MediaStorage::get('app');
    $config = config('media-library.storage.presets.app');

    expect($storage)->toBeInstanceOf(MediaStorage::class);
    expect($storage->name)->toEqual($config['name']);
    expect($storage->disk)->toEqual($config['disk']);
    expect($storage->location)->toEndWith($config['location']);
});

it('should set active storage', function () {
    MediaStorage::use('app');
    $a = MediaStorage::get('app');
    $b = MediaStorage::active();

    expect($b->name)->toEqual($a->name);
    expect($b->disk)->toEqual($a->disk);
    expect($b->location)->toEqual($a->location);
});

it('should set active storage from instance', function () {
    /** @var MediaStorage */
    $a = MediaStorage::create(['name' => 'Products']);
    MediaStorage::use($a);
    $b = MediaStorage::active();

    expect($b->name)->toEqual($a->name);
    expect($b->disk)->toEqual($a->disk);
    expect($b->location)->toEqual($a->location);
});

it('should throw when attempting to use undefined storage', function () {
    MediaStorage::use('system');
})->throws(StorageRequiredException::class);
