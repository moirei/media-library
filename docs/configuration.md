# Configuration

## Publish the config

```bash
php artisan vendor:publish --tag=media-library-config
```

The configuration file will be placed in `config/media-library.php`

## Api Path

Allow the endpoint path in `config/cors.php`. The default is `media`.

```php
...
'paths' => [..., 'media/*'],
...
```

## Middleware

Guard your uploads and mutation endpoints to only allow admin access.

This guards the protected file access routes, browsing and file/folder update endpoints.
Alternatively you can desable certain endpoints if not needed.

```php
...
'route' => [
	...
    'middleware' => [
        ...
        'media.protected' => [
            'auth',
            'can',
        ]
    ],
],
```

To further protect other routes, define the values for each route name.

See all [routes](/routes).
