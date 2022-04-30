<?php

namespace MOIREI\MediaLibrary;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Middleware\SubstituteBindings;
use MOIREI\MediaLibrary\Observers\FileObserver;
use MOIREI\MediaLibrary\Observers\FolderObserver;
use MOIREI\MediaLibrary\Observers\SharedContentObserver;
use MOIREI\MediaLibrary\Observers\AttachmentObserver;
use Illuminate\Routing\Middleware\ValidateSignature;
use MOIREI\MediaLibrary\Http\Middleware\ShareAuth;
use MOIREI\MediaLibrary\Http\Middleware\ShareResolver;
use MOIREI\MediaLibrary\Http\Middleware\ShareAccess;
use MOIREI\MediaLibrary\Http\Middleware\ProtectGroupPlaceholder;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\VerifyCsrfToken;
use MOIREI\MediaLibrary\Console\Clean;
use MOIREI\MediaLibrary\Console\CleanEmptyFolders;
use MOIREI\MediaLibrary\Console\CleanExpiredShareables;
use MOIREI\MediaLibrary\Console\CleanLonelyFiles;
use MOIREI\MediaLibrary\Console\CleanAttachments;
use MOIREI\MediaLibrary\Models\Attachment;
use MOIREI\MediaLibrary\Models\MediaStorage;
use MOIREI\MediaLibrary\Observers\StorageObserver;

class MediaLibraryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Clean::class,
                CleanEmptyFolders::class,
                CleanLonelyFiles::class,
                CleanExpiredShareables::class,
                CleanAttachments::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/media-library.php' => config_path('media-library.php')
            ], 'media-library-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/create_media_tables.php' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_media_tables.php'),
            ], 'media-library-migrations');
        }

        $this->app->booted(function () {
            if (config('media-library.clearn_ups.enabled', false)) {
                $this->registerScheduler();
            }
        });

        $folderClass = config('media-library.models.folder');
        $fileClass = config('media-library.models.file');
        $sharedContentClass = config('media-library.models.shared');

        $folderClass::observe(new FolderObserver());
        $fileClass::observe(new FileObserver());
        $sharedContentClass::observe(new SharedContentObserver());
        MediaStorage::observe(new StorageObserver());
        Attachment::observe(new AttachmentObserver());

        if (config('media-library.route.disabled', false) !== false) {
            $middleware = [SubstituteBindings::class];

            // get global middleware
            foreach (config('media-library.route.middleware', []) as $key => $value) {
                if (!is_string($key) and ($key !== 'media.protected')) {
                    array_push($middleware, $value);
                }
            }

            Route::group([
                'as' => config('media-library.route.name'),
                'prefix' => config('media-library.route.prefix'),
                'domain' => config('media-library.route.domain'),
                'middleware' => $middleware,
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            });

            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'media-library');

            Route::aliasMiddleware('media.signed', ValidateSignature::class);
            Route::aliasMiddleware('media.share.resolve', ShareResolver::class);
            Route::aliasMiddleware('media.share.access', ShareAccess::class);
            Route::aliasMiddleware('media.share.auth', ShareAuth::class);
            Route::pushMiddlewareToGroup('media.share', ShareResolver::class);
            Route::pushMiddlewareToGroup('media.share', ShareAccess::class);
            Route::pushMiddlewareToGroup('media.share', ShareAuth::class);
            Route::pushMiddlewareToGroup('media.session', StartSession::class);
            Route::pushMiddlewareToGroup('media.session', ShareErrorsFromSession::class);
            Route::pushMiddlewareToGroup('media.session', VerifyCsrfToken::class);
            Route::pushMiddlewareToGroup('media.protected', ProtectGroupPlaceholder::class);

            $middlewareProtected = collect(config('media-library.route.middleware'))->get('media.protected', []);
            if (is_string($middlewareProtected)) $middlewareProtected = [$middlewareProtected];
            foreach ($middlewareProtected  as $middleware) {
                Route::pushMiddlewareToGroup('media.protected', $middleware);
            }

            Route::model('folder', $folderClass);
            Route::model('shared', $sharedContentClass);
            Route::bind('file', function ($value) use ($fileClass) {
                if (Api::isUuid($value)) {
                    return $fileClass::findOrFail($value);
                }
                return $fileClass::where('fqfn', $value)->firstOrFail();
            });
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/media-library.php', 'media-library');
        $this->app->instance('mediaApi', new Api);
        $this->app->bind(MediaStorage::class, function ($app) {
            return MediaStorage::active();
        });
    }

    protected function registerScheduler()
    {
        $schedule = $this->app['Illuminate\Console\Scheduling\Schedule'];
        $frequency = config('media-library.clearn_ups.schedule', 'weekly');
        $schedule->command('media:clean')->$frequency();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            MediaStorage::class,
        ];
    }
}
