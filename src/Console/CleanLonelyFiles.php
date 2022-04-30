<?php

namespace MOIREI\MediaLibrary\Console;

use Exception;

class CleanLonelyFiles extends Command
{
    protected $signature = 'media:clean:lonely-files
  {--days= : List results to days after creation},
  {--dry-run : List files that will be removed without removing them},
  {--force : Force operation when in production}';

    protected $description = 'Cleanup lonely files';

    public function handle()
    {
        $fileClass = config('media-library.models.file');

        $query = $fileClass::withTrashed()
            ->whereDoesntHave('fileables')
            ->whereDoesntHave('shares')
            ->whereDoesntHave('model')
            ->where(function ($query) {
                $query->whereDoesntHave('folder')
                    ->orWhereHas('folder', function ($unit) {
                        return $unit->whereDoesntHave('shares');
                    });
            });
        $query = $this->applyAge($query);

        $count = $query->count();
        if ($count <= 0) {
            $this->comment('No lonely files to clean');
            return;
        }

        if ($this->isDryRun()) {
            $fields = ['id', 'name', 'extension', 'location', 'private', 'disk'];
            $files = $query->get($fields)->toArray();
            $this->comment('Cleanable files');
            $this->table($fields, $files);
            return;
        }

        if (!$this->confirmToProceed()) {
            return;
        }

        $key = (new $fileClass)->getKeyName();
        $files = $query->get([$key])->map(fn ($id) => $id->$key)->toArray();

        $this->info('Cleaning lonely files...');
        try {
            $fileClass::whereIn($key, $files)->chunk(100, function ($files) {
                $files->each->forceDelete();
            });

            $this->warn("Cleaned $count lonely files.");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
