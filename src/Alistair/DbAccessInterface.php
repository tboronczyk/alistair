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
     * @param array|null $params
     * @throws \PDOException
     */
    public function query(string $query, ?array $params = null);

    /**
     * Execute a query and return the result rows.
     *
     * @param string $query
     * @param array|null $params
     * @return array
     * @throws \PDOException
     */
    public function queryRows(string $query, ?array $params = null): array;

    /**
     * Execute a query and return a single row.
     *
     * @param string $query
     * @param array|null $params
     * @return array
     * @throws \PDOException
     */
    public function queryRow(string $query, ?array $params = null): array;

    /**
     * Execute a query and return the value of the first column of the first
     * row.
     *
     * @param string $query
     * @param array|null $params
     * @return mixed
     * @throws \PDOException
     */
    public function queryValue(string $query, ?array $params = null) /*: mixed */;
}
