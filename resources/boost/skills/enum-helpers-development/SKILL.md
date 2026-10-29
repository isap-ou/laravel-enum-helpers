---
name: enum-helpers-development
description: "Use when working with PHP enums in a Laravel project that uses isap-ou/laravel-enum-helpers. Trigger when the request mentions enum labels or enum translations, enum values in migrations, database ENUM columns that must match enum cases, exporting enums to JavaScript, or the InteractWithCollection, HasLabel, UpdatableEnumColumns or JsConvertibleEnum names. Tasks include getting enum values and keys as collections, adding translatable labels to enum cases, syncing MySQL enum columns and their defaults with the enum-helpers:migrate:enums command, and generating JS objects with the enum-helpers:js:export command."
license: MIT
metadata:
  author: ISAP OU
---

# Laravel Enum Helpers Development

## When to use this skill

Use this skill when you create or change a PHP enum in an app that has `isap-ou/laravel-enum-helpers` installed. It applies when the enum needs one of these:

- A list of its values or names (for example, for a migration or a validation rule).
- Human-readable, translatable labels.
- A database `ENUM` column that must stay in sync with the enum cases.
- A JavaScript copy of the enum for the frontend.

All helpers read the `->value` of each case. Use backed enums (`enum Status: string`).

## Configuration

Publish the config file to `config/enum-helpers.php`:

```bash
php artisan vendor:publish --tag="enum-helpers-config"
```

Keys:

- `enum_locations` — map of directory to namespace. Default: `['app/Enums' => '\\App\\Enums\\']`. The two Artisan commands only scan these directories.
- `post_migrate` — when truthy, the app runs `enum-helpers:migrate:enums` after each `php artisan migrate`. Set it to `false` to turn this off.
- `label.prefix` — default translation key prefix. Default: `enums`.
- `label.namespace` — default translation namespace (for example, a package namespace). Default: `null`.
- `js_objects_file` — output file of the JS export. Default: `resources/js/enums.js`.

## Collection helpers

Add the `InteractWithCollection` trait to get the cases as Laravel collections:

```php
use IsapOu\EnumHelpers\Concerns\InteractWithCollection;

enum OrderStatus: string
{
    use InteractWithCollection;

    case NEW = 'new';
    case PAID = 'paid';
}

OrderStatus::collection();    // Collection of the case objects
OrderStatus::values();        // ['new', 'paid']
OrderStatus::keys();          // ['NEW', 'PAID']
OrderStatus::keyValuePairs(); // ['NEW' => 'new', 'PAID' => 'paid']
```

Use `values()` to define an enum column in a migration:

```php
$table->enum('status', OrderStatus::values()->toArray());
```

## Sync database enum columns

Implement `UpdatableEnumColumns` and return a map of table to column from `tables()`:

```php
use IsapOu\EnumHelpers\Contracts\UpdatableEnumColumns;

enum OrderStatus: string implements UpdatableEnumColumns
{
    case NEW = 'new';
    case PAID = 'paid';

    public static function tables(): array
    {
        return [
            'orders' => 'status',
        ];
    }

    // Optional. The key is "{table}_{column}". The value is an enum case.
    public static function tableColumnDefaults(): array
    {
        return [
            'orders_status' => self::NEW,
        ];
    }
}
```

Then run the command:

```bash
php artisan enum-helpers:migrate:enums
```

The command runs `ALTER TABLE {table} MODIFY COLUMN {column} ENUM(...)` for each pair, and adds `DEFAULT '{value}'` when `tableColumnDefaults()` has a matching key. It skips tables and columns that do not exist.

After you add a case to such an enum, you do not need a new migration for the column. Run the command, or run `php artisan migrate` when `post_migrate` is on.

## Export enums to JavaScript

Implement the empty marker interface `JsConvertibleEnum`:

```php
use IsapOu\EnumHelpers\Contracts\JsConvertibleEnum;

enum OrderStatus: string implements JsConvertibleEnum
{
    case NEW = 'new';
    case PAID = 'paid';
}
```

Then run the command:

```bash
php artisan enum-helpers:js:export
```

The command writes one line per enum to the `js_objects_file`, and replaces the old content of the file:

```js
export const OrderStatus = Object.freeze({NEW:"new", PAID:"paid"});
```

Run the command again after you change a `JsConvertibleEnum` enum. Do not edit the output file by hand.

## Translatable labels

Add the `HasLabel` trait:

```php
use IsapOu\EnumHelpers\Concerns\HasLabel;

enum OrderStatus: string
{
    use HasLabel;

    case NEW = 'new';
    case PAID = 'paid';
}

OrderStatus::NEW->getLabel();  // trans('enums.OrderStatus.NEW')
OrderStatus::getLabels();      // Collection: ['NEW' => '...', 'PAID' => '...']
```

The translation key is `{namespace}::{prefix}.{EnumShortName}.{CASE_NAME}`. The namespace and the prefix are optional. With the default config, add the labels to `lang/{locale}/enums.php`:

```php
return [
    'OrderStatus' => [
        'NEW' => 'New',
        'PAID' => 'Paid',
    ],
];
```

Both methods accept optional arguments `(?string $prefix, ?string $namespace, ?string $locale)`:

```php
OrderStatus::NEW->getLabel('statuses', 'my-package', 'fr'); // trans('my-package::statuses.OrderStatus.NEW', locale: 'fr')
OrderStatus::getLabels(locale: 'fr');
```

An empty argument falls back to the enum's `getPrefix()` / `getNamespace()`. These methods return the `label.prefix` / `label.namespace` config by default. Override them in one enum to change the key for that enum only:

```php
protected function getPrefix(): ?string
{
    return 'statuses';
}
```

For IDE support, the enum can also implement `IsapOu\EnumHelpers\Contracts\HasLabel`. The trait is also compatible with Filament's `Filament\Support\Contracts\HasLabel`. The interface and the trait have the same short name, so alias one of them:

```php
use Filament\Support\Contracts\HasLabel;
use IsapOu\EnumHelpers\Concerns\HasLabel as HasLabelTrait;

enum OrderStatus: string implements HasLabel
{
    use HasLabelTrait;

    case NEW = 'new';
}
```

## Verification

1. Call `OrderStatus::values()` in Tinker and confirm it returns the case values.
2. Call `OrderStatus::NEW->getLabel()`. If it returns the raw key (for example `enums.OrderStatus.NEW`), the translation is missing from `lang/{locale}/enums.php`.
3. Run `php artisan enum-helpers:migrate:enums` and confirm it prints `Enum column {column} was updated in table {table}.` for each pair.
4. Run `php artisan enum-helpers:js:export` and confirm the enum is in the `js_objects_file`.

## Common pitfalls

- Put enums directly in a directory from `enum_locations`. The commands build the class name from the namespace and the file name only, so they skip enums in subdirectories. To scan a subdirectory, add it to `enum_locations` with its own namespace.
- `enum-helpers:migrate:enums` uses MySQL / MariaDB syntax. On SQLite or PostgreSQL, do not implement `UpdatableEnumColumns`, and set `post_migrate` to `false`.
- The `post_migrate` listener does not run while the app runs unit tests.
