# Laravel Enum Helpers

This package brings some helpers to native [PHP Enums](https://www.php.net/manual/en/language.enumerations.basics.php)


## Installation

You can install the package via composer:

```bash
composer require isap-ou/laravel-enum-helpers
```

You will most likely need to edit the extensive configuration, so you can publish the config file with:

```bash
php artisan vendor:publish --tag="enum-helpers"
```

Default config

```php
return [
    'enum_locations' => [
        'app/Enums' => '\\App\\Enums\\',
    ],

    'label' => [
        'prefix' => null,
        'namespace' => null,
    ],

    'post_migrate' => true,

    'js_objects_file' => 'resources/js/enums.js',
];
```

1. `enum_locations` - path where enums located. Key is directory with enums, value - namespace for specified directory
2. `post_migrate` - enable or disable post migrate event listener
3. `js_objects_file` - path for generated js output
4. `label.prefix` - get default prefix for translations of enum fields
5. `label.namespace` - get default prefix for translations of enum namespace (when using as part of own package)

## Available helpers
### Migration helper

The way easy to add all enums to database column. 

Just add to Enum trait `InteractWithCollection`
```php
use IsapOu\EnumHelpers\Concerns\InteractWithCollection;

enum ExampleEnum: string
{

    use InteractWithCollection;

    case ENUM_ONE = 'enum_one';
    case ENUM_TWO = 'enum_two';
}
```

And in migration class

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('table_name', function (Blueprint $table){
            ...
            $table->enum('enum_column', ExampleEnum::values()->toArray());
            ...
        });
    }
}
```

### Update enum columns in DB

Artisan command allows update available(possible) values for specific enum column.

Modify enum:

```php
use IsapOu\EnumHelpers\Contracts\UpdatableEnumColumns;

enum ExampleEnum: string implements UpdatableEnumColumns
{

    case ENUM_ONE = 'enum_one';
    case ENUM_TWO = 'enum_two';
    
    public static tables()
    {
        return [
            'table_name' => 'enum_column'
        ]
    }
}
```

And run command

```bash
php artisan enum-helpers:migrate:enums
```

There is also a listener enabled by default that will run after a successful migration. 
To disable it edit `enum-helpers.php`: 

```php
<?php 
return [
    ...
    
    'post_migrate' => false,
    
    ...
]
```


### Convert PHP Enums to JS objects

Artisan command allows generate js objects based on enums


Modify enum:

```php
use IsapOu\EnumHelpers\Contracts\UpdatableEnumColumns;

enum ExampleEnum: string implements JsConvertibleEnum
{
    case ENUM_ONE = 'enum_one';
    case ENUM_TWO = 'enum_two';
}
```
And run command

```bash
php artisan enum-helpers:js:export
```

Output will

```js
export const ExampleEnum = Object.freeze({ENUM_ONE: 'enum_one', ENUM_TWO: 'enum_two'})
```

You can specify output path in config `enum-helpers.php`

```php
return [
    ...
    
    'js_objects_file' => 'resources/js/enums.js'
    
    ...
]
```

### Label helper

Label helper allows to transform an enum instance into a textual label. 
This is useful for displaying human-readable translatable enum values in your UI.

```php
use IsapOu\EnumHelpers\Concerns\HasLabel;

enum ExampleEnum: string
{
    use HasLabel;

    case ENUM_ONE = 'enum_one';
    case ENUM_TWO = 'enum_two';
}
```

and get textual label 

```php
ExampleEnum::ENUM_ONE->getLabel()
```

By default it will try to find translation with key e.g. `ExampleEnum.ENUM_ONE`
1. `ExampleEnum` - class name
2. `ENUM_ONE` - enum item name

Method `getLabel` has two optional parameters:
1. `prefix` - Allows prepend prefix for translation key
2. `namespace` - Allows to prepend namespace. Suitable during own package development

There is possibility to define `prefix` and `namespace` globally via `enum-helpers.config` or on enum level via methods 

```php
protected function getPrefix(): ?string
{
    return 'prefix';
}

protected function getNamespace(): ?string
{
    return 'namespace';
}
```

> **Optional**. Can be added interface `\IsapOu\EnumHelpers\Contracts\HasLabel` that can solve ide autocomplete and tips

This helper compatible with [Enums in FilamentPHP](https://filamentphp.com/docs/3.x/support/enums)

```php

use Filament\Support\Contracts\HasLabel;

enum ExampleEnum: string implements HasLabel
{
    use HasLabel;

    case ENUM_ONE = 'enum_one';
    case ENUM_TWO = 'enum_two';
}
```

## Contributing

Please, submit bugs or feature requests via the [Github issues](https://github.com/isap-ou/laravel-enum-helpers/issues).

Pull requests are welcomed!

Thanks!

## License

The AgileCRM Client for Laravel is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


