<?php

namespace MOIREI\MediaLibrary\Console;

use Exception;
use MOIREI\MediaLibrary\Models\Attachment;

class CleanAttachments extends Command
{
    protected $signature = 'media:clean:attachments
    {--days= : List results to days after creation},
    {--dry-run : List attachments that will be removed without removing them},
    {--force : Force operation when in production}';

    protected $description = 'Cleanup pending attachments';

    public function handle()
    {
        $query = Attachment::where('pending', true)->orderBy('id', 'desc');
        $query = $this->applyAge($query);

        $count = $query->count();
        if ($count <= 0) {
            $this->comment('No pending attachments to clean');
            return;
        }

        if ($this->isDryRun()) {
            $fields = ['id', 'filename', 'disk', 'pending'];
            $attachments = $query->get($fields)->toArray();
            $this->comment('Cleanable attachments');
            $this->table($fields, $attachments);
            return;
        }

        if (!$this->confirmToProceed()) {
            return;
        }

        $this->info('Cleaning pending attachments...');
        try {
            $query->chunk(100, function ($attachments) {
                $attachments->each->purge();
            });

            $this->warn("Cleaned $count pending attachments.");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
