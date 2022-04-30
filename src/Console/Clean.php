<?php

namespace MOIREI\MediaLibrary\Console;

class Clean extends Command
{
    protected $signature = 'media:clean
    {--dry-run : List items that will be removed without removing them},
    {--force : Force deletes}';

    protected $description = 'Cleanup unwanted files and folders';

    public function handle()
    {
        $tasks = array_reduce(config('media-library.clean_ups.clean', []), function ($tasks, $task) {
            [$command, $shedule] = explode(':', "$task:"); // predense with ':' to ensure element in array destruction
            $tasks[$command] = $shedule;
            return $tasks;
        }, []);
        $commands = array_keys($tasks);

        $cleaned = false;
        $params = [];
        $params['--force'] = $this->option('force') === true;
        $params['--dry-run'] = $this->option('dry-run') === true;

        // Should clean empty folders
        if (in_array('empty-folders', $commands)) {
            if (!empty($tasks['empty-folders'])) {
                $params['--days'] = $tasks['empty-folders'];
            }
            $this->call('media:clean:empty-folders', $params);
            $cleaned = true;
        }

        // Should clean lonely files
        if (in_array('lonely-files', $commands)) {
            if (!empty($tasks['lonely-files'])) {
                $params['--days'] = $tasks['lonely-files'];
            }
            $this->call('media:clean:lonely-files', $params);
            $cleaned = true;
        }

        // Should clean expired shareables
        if (in_array('expired-shareables', $commands)) {
            if (!empty($tasks['expired-shareables'])) {
                $params['--days'] = $tasks['expired-shareables'];
            }
            $this->call('media:clean:expired-shareables', $params);
            $cleaned = true;
        }

        // Should clean pending attachments
        if (in_array('attachments', $commands)) {
            if (!empty($tasks['attachments'])) {
                $params['--days'] = $tasks['attachments'];
            }
            $this->call('media:clean:attachments', $params);
            $cleaned = true;
        }

        if ($cleaned) {
            $this->info('Operation complete!');
        } else {
            $this->info('Nothing to clean.');
        }
    }
}
