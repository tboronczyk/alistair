<?php

declare(strict_types=1);

namespace Boronczyk\Alistair;

/**
 * Class DbAccess
 * @package Boronczyk\Alistair
 */
class DbAccess implements DbAccessInterface
{
    /**
     * Constructor
     *
     * @param \PDO $pdo
     */
    public function __construct(
        protected \PDO $pdo
    ) {
    }

    /**
     * Prepare and execute a prepared statement
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @return \PDOStatement
     * @throws \PDOException
     */
    protected function stmt(string $query, ?array $params): \PDOStatement
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Return the underlying PDO connection object
     *
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Execute a query
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @return void
     * @throws \PDOException
     */
    public function query(string $query, ?array $params = null): void
    {
        $this->stmt($query, $params);
    }

    /**
     * Execute a query and return the result rows
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @param ?string $classname (optional)
     * @return array<array<string,string>|object>
     * @throws \PDOException
     */
    public function queryRows(string $query, ?array $params = null, ?string $classname = null): array
    {
        $stmt = $this->stmt($query, $params);

        if ($classname == null) {
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        } else {
            /** @phpstan-ignore-next-line */
            $stmt->setFetchMode(\PDO::FETCH_CLASS, $classname);
        }
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();

        return ($rows === false) ? [] : $rows;
    }

    /**
     * Execute a query and return a single row
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @param ?string $classname (optional)
     * @return array<string,string>|object|null
     * @throws \PDOException
     */
    public function queryRow(string $query, ?array $params = null, ?string $classname = null): array|object|null
    {
        $stmt = $this->stmt($query, $params);

        if ($classname == null) {
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        } else {
            /** @phpstan-ignore-next-line */
            $stmt->setFetchMode(\PDO::FETCH_CLASS, $classname);
        }
        $row = $stmt->fetch();
        $stmt->closeCursor();

        return ($row === false) ? null : $row;
    }

    /**
     * Execute a query and return the value of the first column of the first
     * row
     *
     * @param string $query
     * @param ?array<int|string, mixed> $params (optional)
     * @return ?string
     * @throws \PDOException
     */
    public function queryValue(string $query, ?array $params = null): ?string
    {
        $row = (array)$this->queryRow($query, $params);
        $value = reset($row);

        return (count($row)) ? $value : null;
    }
}
