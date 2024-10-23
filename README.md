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
    
    public static function tables(): array
    {
        return [
            'table_name' => 'enum_column'
        ];
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

### Label Helper

The Label helper allows you to transform an enum instance into a textual label, 
making it useful for displaying human-readable, translatable enum values in your UI.

```php
use IsapOu\EnumHelpers\Concerns\HasLabel;

enum ExampleEnum: string
{
    use HasLabel;

    case ENUM_ONE = 'enum_one';
    case ENUM_TWO = 'enum_two';
}
```

You can retrieve a textual label for an enum case using the getLabel method:

```php
ExampleEnum::ENUM_ONE->getLabel()
```

By default, the getLabel method attempts to find a translation key, following this format: `ExampleEnum.ENUM_ONE`.
    1.	`ExampleEnum` - The class name of the enum.
    2.	`ENUM_ONE` - The enum case name.

#### Parameters

The getLabel method accepts three optional parameters:
	1.	`prefix`: Prepends a prefix to the translation key.
	2.	`namespace`: Prepends a namespace to the translation key. This is particularly useful when developing packages.
	3.	`locale`: Allows you to specify the locale for translation. If not provided, the app’s default locale will be used.

##### Example with custom parameters:
```php
ExampleEnum::ENUM_ONE->getLabel('custom_prefix', 'custom_namespace', 'fr');
```
This will retrieve the French (fr) translation with the specified prefix and namespace.

#### getLabels Method

The getLabels method returns a collection of labels for all enum cases, 
making it convenient to retrieve or display translatable labels for multiple enum values at once.

```php
$labels = ExampleEnum::getLabels();

// Output:
// Illuminate\Support\Collection {#1234
//     all: [
//         "ENUM_ONE" => "Enum One Label",
//         "ENUM_TWO" => "Enum Two Label",
//     ],
// }
```

##### Customizing Prefix, Namespace, and Locale

You can customize the prefix, namespace, and locale for the translations when retrieving labels for all cases:
```php
$customLabels = ExampleEnum::getLabels('custom_prefix', 'custom_namespace', 'fr');
```

#### Global Configuration for Prefix and Namespace
You can define the `prefix` and `namespace` globally in the configuration file `enum-helpers.config`, 
or override them on the enum level by defining the following methods:

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

The global or per-enum configurations will be used unless you provide custom values when calling getLabel or getLabels.

> **Optional**. Interface `\IsapOu\EnumHelpers\Contracts\HasLabel` for better IDE support

For better IDE support, you can implement the \IsapOu\EnumHelpers\Contracts\HasLabel interface. 
This helps provide autocomplete suggestions and improves code hinting for the getLabel method when working with enums.

##### FilamentPHP Compatibility
This helper is fully compatible with [Enums in FilamentPHP](https://filamentphp.com/docs/3.x/support/enums)

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


