<?php

namespace MOIREI\MediaLibrary\Console;

use Illuminate\Console\Command as BaseCommand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Command extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * Check if command is a dry run
     *
     * @return bool
     */
    protected function isDryRun(): bool
    {
        return $this->option('dry-run');
    }

    /**
     * Check if the days limit was given
     *
     * @return bool
     */
    protected function hasLimit(): bool
    {
        return !!$this->option('days');
    }

    /**
     * The age of the latest record
     *
     * @param int $default
     * @return Carbon
     */
    protected function getAge($default = 15): Carbon
    {
        $days = $this->option('days');
        return now()->subDays(empty($days) ? $default : intval($days));
    }

    /**
     * The age of the latest record
     *
     * @param Query $query
     * @param string $attribute
     * @return Query
     */
    protected function applyAge(Builder $query, string $attribute = 'created_at'): Builder
    {
        if ($this->hasLimit()) {
            $query = $query->where($attribute, '<=', $this->getAge());
        }
        return $query;
    }

    /**
     * Format input to textual table.
     *
     * @param  array  $attributes
     * @param  array|Collection  $rows
     * @param  string  $tableStyle
     * @param  array  $columnStyles
     * @return void
     */
    public function table($attributes, $rows, $tableStyle = 'default', array $columnStyles = [])
    {
        $headers = array_map(fn ($attribute) => ucfirst($attribute), $attributes);
        $rows = collect($rows)
            // For each row, take only $attributes and make booleans displayable while at it
            ->map(fn ($row) => array_map(fn ($attribute) => is_bool($attribute) ? ($attribute ? 'True' : 'False') : $attribute, Arr::only($row, $attributes)))
            ->toArray();

        // Sort rows after attributes order
        $sorting_array = [];
        foreach ($attributes as $attribute) $sorting_array[$attribute] = true;
        $rows = array_map(fn ($row) => array_replace($sorting_array, $row), $rows);

        parent::table($headers, $rows, $tableStyle, $columnStyles);
    }
}
