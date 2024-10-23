<?php

declare(strict_types=1);

namespace IsapOu\EnumHelpers\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use IsapOu\EnumHelpers\Contracts\UpdatableEnumColumns;
use Symfony\Component\Console\Attribute\AsCommand;

use function base_path;
use function collect;
use function vsprintf;

#[AsCommand('enum-helpers:migrate:enums')]
class MigrateEnumsCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates enum columns in the database for tables defined by enums that implement TableColumnUpdatableEnum interface';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        foreach (Config::get('enum-helpers.enum_locations') as $directory => $namespace) {
            if (! File::exists($directory)) {
                continue;
            }
            $files = File::allFiles(base_path($directory));
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($files as $file) {
                $class = $namespace . $file->getBasename('.php');

                if (! class_exists($class)) {
                    continue;
                }

                if (! \in_array(UpdatableEnumColumns::class, class_implements($class))) {
                    continue;
                }

                $options = collect($class::cases())->pluck('value')->implode("','");
                foreach ($class::tables() as $table => $column) {
                    if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                        continue;
                    }
                    $rawSql = "ALTER TABLE {$table} MODIFY COLUMN {$column} ENUM('{$options}') ";
                    DB::statement($rawSql);
                    $this->info(vsprintf('Enum column %s was updated in table %s.', [
                        $column,
                        $table,
                    ]));
                }
            }
        }

        return 0;
    }
}
