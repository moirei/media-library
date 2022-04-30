<?php

use Illuminate\Support\Facades\Route;
use MOIREI\MediaLibrary\Http\Controllers\ApiController;
use MOIREI\MediaLibrary\Http\Controllers\ShareController;
use MOIREI\MediaLibrary\Http\Controllers\AttachmentController;

if (!function_exists("middleware")) {
    function middleware(string $key): array
    {
        $middleware = collect(config('media-library.route.middleware'))->get($key, []);

        if (is_string($middleware)) {
            $middleware = [$middleware];
        }

        if (func_num_args() > 1) {
            $extra = array_slice(func_get_args(), 1);
            $middleware = array_merge($middleware, $extra);
        }

        return $middleware;
    }
}

// Imaging
if (!config('media-library.route.disabled.magic_imager', true)) {
    Route::get('/img/{file}',                   ApiController::class . '@getPublicImage')->middleware(middleware('file'))->name('image');
}

// File routes
if (!config('media-library.route.disabled.file_url', true)) {
    Route::get('/f/{file}',                         ApiController::class . '@getPublic')->middleware(middleware('file'))->name('file');
    Route::get('/f/{file}/s',                       ApiController::class . '@get')->middleware(middleware('file.signed', 'media.signed'))->name('file.signed');
    Route::get('/f/{file}/download',                ApiController::class . '@downloadPublic')->middleware(middleware('download'))->name('download');
    Route::get('/f/{file}/download/s',              ApiController::class . '@download')->middleware(middleware('download.signed', 'media.signed'))->name('download.signed');
    Route::get('/f/{file}/stream',                  ApiController::class . '@streamPublic')->middleware(middleware('stream'))->name('stream');
    Route::get('/f/{file}/stream/s',                ApiController::class . '@stream')->middleware(middleware('stream.signed', 'media.signed'))->name('stream.signed');
}

// Protected file routes
if (!config('media-library.route.disabled.file_api', true)) {
    Route::middleware('media.protected')->group(function () {
        Route::get('/protected/{file}',                             ApiController::class . '@get')->middleware(middleware('file.protected'))->name('file.protected');
        Route::get('/protected/{file}/download',                    ApiController::class . '@download')->middleware(middleware('download.protected'))->name('download.protected');
        Route::get('/protected/{file}/stream',                      ApiController::class . '@stream')->middleware(middleware('stream.protected'))->name('stream.protected');

        Route::post('/upload',                                      ApiController::class . '@upload')->middleware(middleware('upload'))->name('upload');
        Route::post('/update/{file}',                               ApiController::class . '@update')->middleware(middleware('update'))->name('update');
        Route::post('/move/{file}',                                 ApiController::class . '@move')->middleware(middleware('move'))->name('move');
        Route::delete('/delete/{file}',                             ApiController::class . '@delete')->middleware(middleware('delete'))->name('delete');
        Route::post('/folder/create',                               ApiController::class . '@folderCreate')->middleware(middleware('folder.create'))->name('folder.create');
        Route::post('/folder/update/{folder}',                      ApiController::class . '@folderUpdate')->middleware(middleware('folder.update'))->name('folder.update');
        Route::post('/folder/move/{folder}',                        ApiController::class . '@folderMove')->middleware(middleware('folder.move'))->name('folder.move');
        Route::delete('/folder/delete/{folder}',                    ApiController::class . '@folderDelete')->middleware(middleware('folder.delete'))->name('folder.delete');
        Route::post('/browse',                                      ApiController::class . '@browse')->middleware(middleware('browse'))->name('browse');
        Route::get('/downloadable-link/{file}',                     ApiController::class . '@downloadableLink')->middleware(middleware('downloadable-link'));
        Route::post('/downloadable-link/{file}',                    ApiController::class . '@downloadableLink')->middleware(middleware('downloadable-link'))->name('downloadable-link');
        Route::post('/shareable-link/{file}',                       ApiController::class . '@shareablebleLink')->middleware(middleware('share'))->name('file.share');
        Route::post('/shareable-link/folder/{folder}',              ApiController::class . '@shareablebleLink')->middleware(middleware('folder.share'))->name('folder.share');

        Route::post('/attachment',                                  AttachmentController::class . '@store')->middleware(middleware('attachment.store'))->name('attachment.store');
        Route::delete('/attachment/{attachment}',                   AttachmentController::class . '@destroy')->middleware(middleware('attachment.destroy'))->name('attachment.destroy');
    });
}

// File share routes
if (!config('media-library.route.disabled.file_share', true)) {
    Route::prefix('share')->middleware('media.session')->group(function () {
        Route::get('/{shared}',                 ShareController::class . '@get')->middleware(middleware('share', 'media.share'))->name('share');
        Route::get('/{shared}/auth',            ShareController::class . '@auth')->middleware(middleware('share.auth', 'media.share.resolve'))->name('share.auth');
        Route::post('/{shared}/auth',           ShareController::class . '@postAuth')->middleware(middleware('share.auth.post', 'media.share.resolve'))->name('share.auth.post');
        Route::get('/{shared}/download',        ShareController::class . '@download')->middleware(middleware('share.download', 'media.share'))->name('share.download');
        Route::post('/{shared}/upload',         ShareController::class . '@upload')->middleware(middleware('share.upload', 'media.share'))->name('share.upload');
        Route::get('/{shared}/signout',         ShareController::class . '@signout')->middleware(middleware('share.signout', 'media.share'))->name('share.signout');
    });
}
