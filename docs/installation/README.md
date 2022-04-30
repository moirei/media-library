---
title: Installation
lang: en-AU
prev: /
---

# Installation


```bash
composer require moirei/media-library
```

## Prepare the database

```php
php artisan vendor:publish --tag=media-library-migrations
```

Then run the migrations

```bash
php artisan migrate
```

## Symlinks

Setup symbolic links in order to out url access to local public storage.

```bash
php artisan storage:link
```


## Scheduling

For auto clean commands to run, be sure to setup the scheduler.

If you haven't already started Laravel scheduler, you'll need to add the following Cron entry to your server.

```bash
* * * * * php artisan schedule:run >> /dev/null 2>&1
```



