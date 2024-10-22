<?php

declare(strict_types=1);

return [
    'enum_locations' => [
        'app/Enums' => '\\App\\Enums\\',
    ],

    'post_migrate' => [
        'enum-helpers:migrate:enums',
    ],

    'js_objects_file' => 'resources/js/enums.js',
];
