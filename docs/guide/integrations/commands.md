# Artisan Commands

All commands accept the following arguments:

| Flag        | Description                                           |
| ----------- | ----------------------------------------------------- |
| `--dry-run` | List items that will be removed without removing them |
| `--force`   | Force operation in production environment             |

**Clean empty folders**

The `days` argument is optional; limit results after the given days.

```bash
php artisan media:clean:empty-folders --days=7
```

**Clean lonely files**

The `days` argument is optional; limit results after the days

```bash
php artisan media:clean:lonely-files --days=7
```

**Expired shared content**

```bash
php artisan media:clean:expired-shareables
```

**Pending attachment**

The `days` argument is optional; limit results after the days

```bash
php artisan media:clean:attachments --days=7
```

**Run all clean commands**

Attempts to run all the above commands according your `clean_ups` configuration.

```bash
php artisan media:clean
```
