<?php

namespace MOIREI\MediaLibrary\Console;

use Exception;
use MOIREI\MediaLibrary\Models\Folder;

class CleanEmptyFolders extends Command
{
    protected $signature = 'media:clean:empty-folders
    {--days= : List results to days after creation},
    {--dry-run : List folders that will be removed without removing them},
    {--force : Force operation when in production}';

    protected $description = 'Cleanup empty folders';

    public function handle()
    {
        $folderClass = config('media-library.models.folder');
        $query = $folderClass::withTrashed()
            ->whereDoesntHave('folders')
            ->whereDoesntHave('files')
            ->whereDoesntHave('shares')
            ->where(function ($query) {
                $query->whereDoesntHave('parent')
                    ->orWhereHas('parent', function ($unit) {
                        return $unit->whereDoesntHave('shares');
                    });
            });
        $query = $this->applyAge($query);


        $count = $query->count();
        if ($count <= 0) {
            $this->comment('No empty folders to clean');
            return;
        }

        if ($this->isDryRun()) {
            $fields = ['id', 'name', 'location', 'private', 'disk'];
            $folders = $query->get($fields)->toArray();
            $this->comment('Cleanable folders');
            $this->table($fields, $folders);
            return;
        }

        if (!$this->confirmToProceed()) {
            return;
        }

        $key = (new $folderClass)->getKeyName();
        $folders = $query->get([$key])->map(fn ($id) => $id->$key)->toArray();

        $this->info('Cleaning empty folders...');
        try {
            $folderClass::whereIn($key, $folders)->chunk(100, function ($folders) {
                $folders->each->forceDelete();
            });

            $this->warn("Cleaned $count empty folders.");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
