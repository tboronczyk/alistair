<?php

declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Interface DbAccessInterface
 * @package Boronczyk\Alistair
 */
interface DbAccessInterface
{
    /**
     * Execute a query.
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @return void
     * @throws \PDOException
     */
    public function query(string $query, ?array $params = null): void;

    /**
     * Execute a query and return the result rows.
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @param ?string $classname (optional)
     * @return array<array<string,string>>
     * @throws \PDOException
     */
    public function queryRows(string $query, ?array $params = null, ?string $classname = null): array;

    /**
     * Execute a query and return a single row.
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @param ?string $classname (optional)
     * @return array<string,string>|object
     * @throws \PDOException
     */
    public function queryRow(string $query, ?array $params = null, ?string $classname = null) /*: array|object */;

    /**
     * Execute a query and return the value of the first column of the first
     * row.
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @return ?string
     * @throws \PDOException
     */
    public function queryValue(string $query, ?array $params = null): ?string;
}
