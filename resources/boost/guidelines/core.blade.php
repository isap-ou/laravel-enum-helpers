## Laravel Enum Helpers

- `isap-ou/laravel-enum-helpers` adds helpers to native PHP enums: values and keys as collections, translatable labels, sync of database enum columns, and export of enums to JavaScript.
- Always activate the `enum-helpers-development` skill when working with PHP enums, or with any code that uses the `InteractWithCollection` or `HasLabel` traits, the `UpdatableEnumColumns` or `JsConvertibleEnum` interfaces, or the `enum-helpers:migrate:enums` or `enum-helpers:js:export` commands.
- If the skill is unavailable, read `vendor/isap-ou/laravel-enum-helpers/resources/boost/skills/enum-helpers-development/SKILL.md` before making changes.
