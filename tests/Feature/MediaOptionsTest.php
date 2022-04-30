<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;
use MOIREI\MediaLibrary\MediaOptions;
use MOIREI\MediaLibrary\Models\Folder;
use MOIREI\MediaLibrary\Models\MediaStorage;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'media-options');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
});

it('should create MediaOptions', function () {
    expect(MediaOptions::make())->toBeInstanceOf(MediaOptions::class);
});

it('expects MediaOptions to have defaults', function () {
    $options = MediaOptions::make();

    /** @var MediaStorage */
    $storage = $options->get('storage');
    $private = $options->get('private');
    $types = $options->get('types');

    expect($storage)->toBeInstanceOf(MediaStorage::class);
    expect($private)->toBeBool();
    expect($private)->toEqual($storage->private);
    expect($types)->toBeArray();
    expect($types)->toEqual(config('media-library.uploads.types'));
});

it('expects MediaOptions storage to use active storage', function () {
    $storageA = MediaStorage::create(['name' => 'Products']);
    MediaStorage::use($storageA);

    $options = MediaOptions::make();
    $storageB = $options->get('storage');

    expect($storageB->name)->toEqual($storageA->name);
    expect($storageB->is($storageA))->toBeTrue();
});

it('expects MediaOptions to NOT have folder but location', function () {
    $storage = MediaStorage::create(['name' => 'Products']);
    MediaStorage::use($storage);

    $options = MediaOptions::make();

    expect($options->get('folder'))->toBeNull();
    expect($options->get('location'))->toBeNull();
});

it('expects MediaOptions to set location', function () {
    $storage = MediaStorage::create(['name' => 'Products']);
    MediaStorage::use($storage);

    $options = MediaOptions::make()->location('products');

    expect($options->get('location'))->toEqual('products');
    expect($options->get('folder'))->toBeInstanceOf(Folder::class);
    expect($options->get('folder')->name)->toEqual('products');
});

it('expects MediaOptions to set location from location', function () {
    $storage = MediaStorage::create(['name' => 'Products']);
    MediaStorage::use($storage);

    $options = MediaOptions::make()->folder('images/products');

    expect($options->get('location'))->toEqual('images/products');
    expect($options->get('folder'))->toBeInstanceOf(Folder::class);
    expect($options->get('folder')->name)->toEqual('products');
    expect($options->get('folder')->location)->toEqual('images');
});
