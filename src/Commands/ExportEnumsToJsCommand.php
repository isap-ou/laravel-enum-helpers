<?php

declare(strict_types=1);

namespace IsapOu\EnumHelpers\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use IsapOu\EnumHelpers\Contracts\JsConvertibleEnum;
use Symfony\Component\Console\Attribute\AsCommand;

use function base_path;
use function collect;
use function vsprintf;

#[AsCommand('enum-helpers:js:export')]
class ExportEnumsToJsCommand extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts enums that implement JsConvertibleEnum interface to JavaScript objects and exports them to a file or output.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $jsString = '';

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

                if (! \in_array(JsConvertibleEnum::class, class_implements($class))) {
                    continue;
                }

                $data = collect($class::cases())->map(fn ($item) => \sprintf('%s:"%s"', $item->name, $item->value));

                $jsString .= vsprintf('export const %s = Object.freeze({%s});', [
                    $file->getBasename('.php'),
                    $data->implode(', '),
                ]);

                $jsString .= PHP_EOL;
            }
        }

        if (! empty($jsString)) {
            File::put(base_path(Config::get('js_objects_file')), trim($jsString));
        }

        return 0;
    }
}
