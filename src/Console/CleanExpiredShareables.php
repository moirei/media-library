<?php

namespace MOIREI\MediaLibrary\Console;

use Exception;

class CleanExpiredShareables extends Command
{
    protected $signature = 'media:clean:expired-shareables
        {--days= : List results to days after creation},
        {--dry-run : List items that will be removed without removing them},
        {--force : Force operation when in production}';

    protected $description = 'Cleanup expired shareables';

    public function handle()
    {
        $sharedContentClass = config('media-library.models.shared');
        $query = $sharedContentClass::where('expires_at', '<=', now())->withTrashed();
        $query = $this->applyAge($query);

        $count = $query->count();
        if ($count <= 0) {
            $this->comment('No expired shareables to clean');
            return;
        }

        if ($this->isDryRun()) {
            $fields = ['id', 'name', 'public'];
            $shareables = $query->get($fields)->toArray();
            $this->comment('Cleanable shareables');
            $this->table($fields, $shareables);
            return;
        }

        if (!$this->confirmToProceed()) {
            return;
        }

        $key = (new $sharedContentClass)->getKeyName();
        $shareables = $query->get([$key])->map(fn ($id) => $id->$key)->toArray();

        $this->info('Cleaning expired shareables...');
        try {
            $sharedContentClass::whereIn($key, $shareables)->chunk(100, function ($shareables) {
                $shareables->each->forceDelete();
            });

            $this->warn("Cleaned $count expired shareables.");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
