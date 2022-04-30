<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MOIREI\MediaLibrary\MagicImager;
use MOIREI\MediaLibrary\MediaLibraryServiceProvider;

uses(DatabaseMigrations::class, RefreshDatabase::class)->group('media', 'magic-imager');

beforeEach(function () {
    include_once __DIR__ . '/../../database/migrations/create_media_tables.php';
    (new \CreateMediaTables)->up();
    app()->register(MediaLibraryServiceProvider::class);
});

it('should create MagicImager', function () {
    expect(MagicImager::make([]))->toBeInstanceOf(MagicImager::class);
});

it('should rename alternate options', function () {
    $options = [
        'width' => 300,
        'height' => 400,
    ];
    $data = MagicImager::make($options)->toArray();

    expect($data)->toBeArray();
    expect($data)->toHaveKeys(['width', 'height']);
    expect($data['width'])->toEqual(300);
    expect($data['height'])->toEqual(400);
});

it('should validate options', function () {
    $magic = MagicImager::make(['height' => 400]);
    expect($magic->validate())->toEqual(true);
});

it('should NOT validate options', function () {
    $magic = MagicImager::make(['height' => 4000]);
    expect($magic->validate())->toEqual(false);
});

it('should validate options with array', function () {
    $magic = MagicImager::make(['border' => [2, '#000000']]);
    expect($magic->validate())->toEqual(true);
});

it('should validate func option', function () {
    $magic = MagicImager::make(['func' => ['grey']]);
    expect($magic->validate())->toEqual(true);
});

it('should NOT validate options with array', function () {
    $magic = MagicImager::make(['border' => [2000, '#000000']]);
    expect($magic->validate())->toEqual(false);

    $magic = MagicImager::make(['border' => [2, '#0000000']]);
    expect($magic->validate())->toEqual(false);
});

it('should require second crop param if first is set', function () {
    $magic = MagicImager::make(['crop' => [100, 100]]);
    expect($magic->validate())->toEqual(true);

    $magic = MagicImager::make(['crop' => [100]]);
    expect($magic->validate())->toEqual(false);
});

it('should throw when invalid options', function () {
    $magic = MagicImager::make(['border' => [2000, '#000000']]);
    $magic->validate(true);
})->throws(\Illuminate\Validation\ValidationException::class);
