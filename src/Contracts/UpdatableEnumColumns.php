<?php

declare(strict_types=1);

namespace IsapOu\EnumHelpers\Contracts;

interface UpdatableEnumColumns
{
    /**
     * Returns an array of tables and corresponding columns that need to be updated.
     *
     * Example of the returned array:
     * [
     *     'users' => 'status',
     *     'orders' => 'order_status',
     * ]
     *
     * @return array<string, string> Array where the key is the table name and the value is the column name.
     */
    public static function tables(): array;
}
